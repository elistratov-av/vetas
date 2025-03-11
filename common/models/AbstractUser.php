<?php

namespace app\common\models;

use app\models\db\ActiveRecord;
use app\models\db\PasswordHistory;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * Class AbstractUser
 * @package app\common\models
 *
 * @property int    $id
 * @property string $login
 * @property string $password
 * @property string $block_until
 * @property bool   $is_blocked
 * @property bool   $is_temp_password
 * @property string $password_valid_till
 * @property string $password_valid_till_min
 * @property string $loginAttemptsTable
 * @property int    $logTarget
 */
abstract class AbstractUser extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_MARK_DELETED = 'mark_deleted';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return static
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @throws \yii\base\Exception
     */
    public function setAuthKey()
    {
        $this->auth_key = \Yii::$app->security->generateRandomString();
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @param string $login
     * @return static
     */
    public static function findByLogin($login)
    {
        $login = str_replace(['_', '%'], ['\_', '\%'], $login);

        return static::find()
            ->where(['ilike', 'login', $login, false])
            ->one();
    }

    /**
     * Проверяет неудачные попытки авторизации за последние 30 минут
     * @return bool
     */
    public function checkLoginAttempts()
    {
        $allowedLoginAttempts = 10;

        $date = new \DateTime();
        $date->sub(new \DateInterval('P30M'));
        $count = (new Query())
            ->from($this->loginAttemptsTable)
            ->where(['>', 'date', $date->format('Y-m-d H:i:s')])
            ->andWhere(['id_user' => $this->id])
            ->count();

        return $count < $allowedLoginAttempts;
    }

    /**
     * Сохраняет неудачную попытку логина
     */
    public function addLoginAttempt()
    {
        return \Yii::$app->db
            ->createCommand()
            ->insert($this->loginAttemptsTable, [
                'id_user' => $this->id,
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            ])
            ->execute();
    }

    /**
     * Блокирует пользователя на 30 минут
     */
    public function blockUntil()
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT5M'));
        $dateTo = $date->format('Y-m-d H:i:s');
        $this->block_until = $dateTo;
        $result = $this->save(true, ['block_until', 'updated_at']);
        $this->logBlockUntil($result, $dateTo);
        if ($result === true) {
            // очистить попытки
            \Yii::$app->db
                ->createCommand()
                ->delete($this->loginAttemptsTable, [
                    'id_user' => $this->id,
                ])
                ->execute();
        }
    }

    /**
     * Проверяет блокировку до времени
     * @return bool
     */
    public function isBlockedUntil()
    {
        if ($this->block_until) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->block_until);
            if (time() < $date->getTimestamp()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param bool   $result
     * @param string $dateTo
     */
    abstract protected function logBlockUntil($result, $dateTo);

    /**
     * @param string $password
     * @param bool   $temporary
     * @return bool
     * @throws \yii\base\Exception
     */
    public function setPassword($password, $temporary = false)
    {
        // проверяем минимальное время действия пароля
        if ($temporary === false && !empty($this->password_valid_till_min) && $this->password_valid_till_min > date('Y-m-d H:i:s')) {
            $this->addError('password', 'Вы можете установить новый пароль не ранее ' . \DateTime::createFromFormat('Y-m-d H:i:s', $this->password_valid_till_min)->format('H:i:s d.m.Y'));

            return false;
        }
        // сравниваем с текущим установленным паролем
        if (!empty($this->password) && $this->validatePassword($password)) {
            $this->addError('password', 'Новый пароль не может совпадать с предыдущим паролем');

            return false;
        }
        // проверяем минимальное количество измененных символов
        if ($temporary === false && !empty($this->password) && !$this->satisfiesMinChanged($password)) {
            $this->addError('password', 'Вам следует изменить больше символов в новом пароле по сравнению с предыдущим');

            return false;
        }
        // сравниваем еще и с предыдущими многоразовыми паролями
        $lastPasswords = PasswordHistory::findLast($this->id, $this->logTarget);
        if (!empty($lastPasswords)) {
            if ($this->is_temp_password !== true) {
                array_shift($lastPasswords);
            }
            foreach ($lastPasswords as $lastPassword) {
                if ($this->validatePassword($password, $lastPassword->password)) {
                    $this->addError('password', 'Новый пароль не может совпадать с предыдущим паролем');

                    return false;
                }
            }
        }

        $this->password = $this->createPasswordHash($password);
        $incrMax = $temporary ? 1 : ArrayHelper::getValue(\Yii::$app->params, 'password_duration', 120);
        $this->password_valid_till = (new \DateTime())->modify('+' . $incrMax . ' day')->format('Y-m-d H:i:s');
        $incrMin = ArrayHelper::getValue(\Yii::$app->params, 'password_min_duration', 1);
        $this->password_valid_till_min = $temporary ? null : (new \DateTime())->modify('+' . $incrMin . ' hours')->format('Y-m-d H:i:s');
        $this->is_temp_password = $temporary;

        return true;
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function savePassword()
    {
        $this->setAuthKey();

        return $this->save(true, [
            'password',
            'password_valid_till',
            'password_valid_till_min',
            'is_temp_password',
            'auth_key',
            'updated_at',
            'updated_by',
        ]);
    }

    /**
     * Для задания параметра конфига passwordEncryptionKey следует получить случайные байты и преобразовать их в base64-строку:
     * ```
     *   $bytes = \Yii::$app->security->generateRandomKey();
     *   $passwordEncryptionKey = base64_encode($bytes);
     * ```
     *
     * @param string $password
     * @return string
     * @throws \yii\base\Exception
     */
    protected function createPasswordHash($password)
    {
        $keyString = ArrayHelper::getValue(\Yii::$app->params, 'passwordEncryptionKey');
        if (empty($keyString)) {
            throw new InvalidConfigException('You should specify passwordEncryptionKey');
        }
        $key = base64_decode($keyString);

        return base64_encode(\Yii::$app->security->encryptByKey($password, $key));
    }

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function validatePassword($password, $hash = null)
    {
        $hash = $hash ?? $this->password;

        if ($this->hasHashedPassword($hash)) {
            // старые пароли, созданные функцией password_hash()
            return \Yii::$app->security->validatePassword($password, $hash);
        }

        // новые пароли с обратимым шифрованием
        $keyString = ArrayHelper::getValue(\Yii::$app->params, 'passwordEncryptionKey');
        if (empty($keyString)) {
            throw new InvalidConfigException('You should specify passwordEncryptionKey');
        }
        $key = base64_decode($keyString);
        $sample = \Yii::$app->security->decryptByKey(base64_decode($hash), $key);

        return $password === $sample;
    }

    /**
     * Старые пароли, созданные функцией password_hash()
     * @param string $hash
     * @return bool
     */
    protected function hasHashedPassword($hash = null)
    {
        $hash = $hash ?? $this->password;

        return substr($hash, 0, 7) === '$2y$13$';
    }

    /**
     * Проверка минимального количества измененных символов в новом пароле
     * @param string $password
     * @return bool
     */
    protected function satisfiesMinChanged($password)
    {
        $limit = ArrayHelper::getValue(\Yii::$app->params, 'password_min_changed_symbols');
        if (empty($limit)) {
            return  true;
        }

        if ($this->hasHashedPassword()) {
            // мы не сможем проверить это для старого пароля
            return true;
        }

        $keyString = ArrayHelper::getValue(\Yii::$app->params, 'passwordEncryptionKey');
        if (empty($keyString)) {
            throw new InvalidConfigException('You should specify passwordEncryptionKey');
        }
        $key = base64_decode($keyString);
        $previous = \Yii::$app->security->decryptByKey(base64_decode($this->password), $key);

        $new = str_split($password);
        $old = str_split($previous);
        $diff1 = array_diff($new, $old);
        $diff2 = array_diff($old, $new);

        return count($diff1) >= $limit || count($diff2) >= $limit || abs(count($new) - count($old)) >= $limit;
    }

    /**
     * @return bool
     */
    public function needsChangePassword()
    {
        if ($this->is_temp_password === true) {
            return true;
        }
        if ($this->password_valid_till === null) {
            return true;
        }
        //if (empty($this->password) || $this->hasHashedPassword()) {
        if (empty($this->password) ) {
            return true;
        }

        try {
            $today = new \DateTime();
            $valid_till = \DateTime::createFromFormat('Y-m-d H:i:s', $this->password_valid_till);
            $result = ($today >= $valid_till);
        } catch (\Throwable $e) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return bool
     */
    abstract public function markDeleted();
}
