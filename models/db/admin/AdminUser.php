<?php

namespace app\models\db\admin;

use app\common\models\AbstractUser;
use app\common\models\UserModel;
use app\common\validators\UniqueValidator;
use app\models\db\PasswordHistory;
use app\modules\adminfstek\components\UserLogManager;
use yii\base\NotSupportedException;

/**
 * Class AdminUser
 * @package  app\models\db\admin
 *
 * @property int    $id
 * @property string $login
 * @property int    $role
 * @property string $password
 * @property string $last_login
 * @property string $ip
 * @property string $created_at
 * @property string $updated_at
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $email
 * @property bool   $is_temp_password
 * @property string $password_valid_till
 * @property string $f_fio
 * @property string $i_fio
 * @property string $o_fio
 * @property bool   $is_blocked
 * @property string $block_until
 * @property string $fullname
 * @property string $password_valid_till_min
 * @property string $auth_key
 * @property bool   $is_deleted
 */
class AdminUser extends AbstractUser
{
    const STATUS_BLOCKED = true;
    const STATUS_NOT_BLOCKED = false;

    const ROLE_ADMIN = 1;
    const ROLE_SECURITY = 2;
    const ROLE_ACCOUNTS_MANAGER = 3;

    /**
     * @var int
     */
    protected $loginAttemptsTable = 'admin.login_attempts';
    /**
     * @var int
     */
    protected $logTarget = PasswordHistory::TARGET_ADMIN;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'admin.users_fstek';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['login', 'role', 'email', 'f_fio', 'i_fio'], 'required', 'on' => [self::SCENARIO_DEFAULT]],
            [['login', 'f_fio', 'i_fio', 'o_fio'], 'filter', 'filter' => 'trim', 'on' => [self::SCENARIO_DEFAULT]],
            [['login', 'f_fio', 'i_fio', 'o_fio'], 'filter', 'filter' => 'strip_tags', 'on' => [self::SCENARIO_DEFAULT]],
            [['login', 'f_fio', 'i_fio', 'o_fio'], 'string', 'min' => 2, 'max' => 255, 'on' => [self::SCENARIO_DEFAULT]],
            ['login', UniqueValidator::class, 'on' => [self::SCENARIO_DEFAULT]],
            ['email', 'email', 'on' => [self::SCENARIO_DEFAULT]],
            ['email', UniqueValidator::class, 'on' => [self::SCENARIO_DEFAULT]],
            ['email', function ($attribute, $params, $validator) {
                $userModel = UserModel::find()->where(['email' => $this->email])->one();
                if ($userModel) {
                    $error = "Значение «$this->email"."» для «Email» уже занято.";
                    $this->addError($attribute, $error);
                }
            }, 'on' => [self::SCENARIO_DEFAULT]],
            ['role', 'in', 'range' => self::roles(), 'on' => [self::SCENARIO_DEFAULT]],
            [['is_blocked', 'is_deleted'], 'boolean', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_MARK_DELETED]],
            [['is_blocked', 'is_deleted'], 'default', 'value' => false, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_MARK_DELETED]],
            [['password', 'last_login', 'ip', 'is_temp_password', 'password_valid_till', 'block_until', 'password_valid_till_min', 'auth_key'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'login' => 'Логин',
            'role' => 'Роль',
            'f_fio' => 'Фамилия',
            'i_fio' => 'Имя',
            'o_fio' => 'Отчество',
            'is_blocked' => 'Заблокирован постоянно',
            'block_until' => 'Заблокирован до',
            'last_login' => 'Последний визит',
            'is_deleted' => 'Удален',
        ];
    }

    /**
     * @param mixed $token
     * @param null  $type
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @param $login
     * @return static
     */
    public static function findByUsername($login)
    {
        return static::findOne(['login' => $login]);

    }

    /**
     * @param $token
     * @return static
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
        ]);
    }

    /**
     * @param $token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = \Yii::$app->params['admin.passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /**
     * @return array
     */
    public static function blockStatuses()
    {
        return [
            self::STATUS_BLOCKED,
            self::STATUS_NOT_BLOCKED,
        ];
    }

    /**
     * @return array
     */
    public static function blockStatusOptions()
    {
        return [
            self::STATUS_BLOCKED => 'Да',
            self::STATUS_NOT_BLOCKED => 'Нет',
        ];
    }

    /**
     * @return array
     */
    public static function roles()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SECURITY,
            self::ROLE_ACCOUNTS_MANAGER,
        ];
    }

    /**
     * @return array
     */
    public static function roleOptions()
    {
        return [
            self::ROLE_ADMIN => 'Администратор',
            self::ROLE_SECURITY => 'Администратор ИБ',
            self::ROLE_ACCOUNTS_MANAGER => 'Менеджер УЗ',
        ];
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        $str = '';
        if (!empty($this->f_fio)) {
            $str .= $this->f_fio;
            $str .= ' ';
        }
        if (!empty($this->i_fio)) {
            $str .= $this->i_fio;
            $str .= ' ';
        }
        if (!empty($this->o_fio)) {
            $str .= $this->o_fio;
            $str .= ' ';
        }

        return trim($str);
    }

    /**
     * @param bool   $result
     * @param string $dateTo
     */
    protected function logBlockUntil($result, $dateTo)
    {
        UserLogManager::autoAuthErrorAdminUserBlock($this, $result, $dateTo);
    }

    /**
     * @return bool
     */
    public function markDeleted()
    {
        $this->scenario = self::SCENARIO_MARK_DELETED;

        $this->is_deleted = true;
        $this->is_blocked = true;
        $this->f_fio = 'УЗ удалена';
        $this->i_fio = '[id ' . $this->id . ']';
        $this->o_fio = null;
        $this->email = null;

        return $this->save(true, [
            'is_deleted',
            'is_blocked',
            'f_fio',
            'i_fio',
            'o_fio',
            'email',
            'updated_at',
            'updated_by',
        ]);
    }
}
