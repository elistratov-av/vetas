<?php

namespace app\common\models;

use Yii;
use app\components\Tree;
use app\models\db\Organizations;
use app\modules\admin\models\Organization;
use app\models\db\Specialists;
use app\models\db\Users;
use app\models\db\admin\AdminUser;
use app\models\db\admin\SecuritySettings;
use app\models\db\PasswordHistory;
use app\common\validators\UniqueValidator;
use app\modules\adminfstek\components\UserLogManager;
use Lcobucci\JWT\Token;
use yii\web\IdentityInterface;
use yii\db\Expression;

/**
 * @property int     $id          [integer]
 * @property string  $login       [varchar(255)]
 * @property string  $password    [varchar(255)]
 * @property integer $created_by
 * @property integer $updated_by
 * @property string  $created_at
 * @property string  $updated_at
 * @property bool    $is_blocked
 * @property string  $last_login  [datetime]
 * @property string  $block_until [datetime]
 * @property string  $f_fio
 * @property string  $i_fio
 * @property string  $o_fio
 * @property string  $fullname
 * @property string  $birthday
 * @property string  $sex
 * @property integer $photo
 * @property string  $auth_key
 * @property string  $email
 * @property bool    $is_temp_password
 * @property string  $password_valid_till
 * @property string  $password_valid_till_min
 * @property bool    $is_deleted
 *
 * @property void   $authKey
 * @property int    $id_specialist
 * @property Specialists   $specialist
 * @property Specialists[] $specialists
 */
class UserModel extends AbstractUser
{
    /**
     * @var string
     */
    protected $loginAttemptsTable = 'public.login_attempts';
    /**
     * @var int
     */
    protected $logTarget = PasswordHistory::TARGET_FRONTEND;

    /**
     * @var int
     */
    private $_id_specialist;

    private $_nested_organisations;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Логин',
            'password' => 'Пароль',
            'f_fio' => 'Фамилия',
            'i_fio' => 'Имя',
            'o_fio' => 'Отчество',
            'birthday' => 'Дата рождения',
            'sex' => 'Пол',
            'photo' => 'Фото',
            'fullname' => 'Ф.И.О.',
            'is_blocked' => 'Заблокирован постоянно',
            'block_until' => 'Заблокирован до',
            'last_login' => 'Последний визит',
            'is_deleted' => 'Удален',
            'sudir_uid' => 'Удален',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login', 'f_fio', 'i_fio', 'sex', 'birthday', 'email'], 'required', 'on' => [self::SCENARIO_DEFAULT]],
            [['login'], 'string', 'max' => 255, 'on' => [self::SCENARIO_DEFAULT]],
            [['f_fio'], 'string', 'max' => 150, 'on' => [self::SCENARIO_DEFAULT]],
            [['i_fio', 'o_fio'], 'string', 'max' => 50, 'on' => [self::SCENARIO_DEFAULT]],
            [['login'], UniqueValidator::class, 'on' => [self::SCENARIO_DEFAULT]],
            ['email', 'email', 'on' => [self::SCENARIO_DEFAULT]],
            ['email', UniqueValidator::class, 'on' => [self::SCENARIO_DEFAULT]],
            ['email', function ($attribute, $params, $validator) {
                $adminUserWithEmail = AdminUser::find()->where(['email' => $this->email])->one();
                if ($adminUserWithEmail) {
                    $error = "Значение «$this->email"."» для «Email» уже занято.";
                    $this->addError($attribute, $error);
                }
            }, 'on' => [self::SCENARIO_DEFAULT]],
            [['birthday'], 'date', 'format' => 'php:Y-m-d', 'on' => [self::SCENARIO_DEFAULT]],
            [['is_blocked', 'is_deleted'], 'boolean', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_MARK_DELETED]],
            [['is_blocked', 'is_deleted'], 'default', 'value' => false, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_MARK_DELETED]],
            [['password', 'last_login', 'photo', 'fullname', 'block_until', 'auth_key'], 'safe'],
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'is_temp_password', 'password_valid_till', 'password_valid_till_min', 'sudir_uid'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (!$token instanceof Token) {
            return null;
        }

        if (!$token->hasClaim('uid') || !$token->hasClaim('pas') || !$token->hasClaim('auth_key')) {
            return null;
        }

        $id = $token->getClaim('uid');
        if (empty($id)) {
            return null;
        }

        if (!$token->hasClaim('pas')) {
            throw new \Exception('Формат токена аутентификации был изменен, необходимо повторно совершить вход в систему.');
        }

        $user = static::findOne(['id' => $id]);
        $password = $token->getClaim('pas');
        $auth_key = $token->getClaim('auth_key');

        if ($user === null || empty($password) || $user->password !== $password || empty($auth_key) || !$user->validateAuthKey($auth_key)) {
            return null;
        }

        $id_specialist = $token->getClaim('id_specialist');
        if ($id_specialist === null && \Yii::$app->controller->action->getUniqueId() != 'v2/user/user/select-organization') {
            return null;
        }

        $user->setId_specialist($id_specialist);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return Specialists::find()
            ->andWhere([
                'id_user' => $this->id,
                'id' => $this->id_specialist,
            ]);
    }

     /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        if ($this->specialist === null || empty($this->specialist->id_organization)) {
            throw new InvalidConfigException();
        }

        return Organization::findOne($this->specialist->id_organization);
    }

    public function getNestedOrganizations ()
    {
        if (is_null($this->_nested_organisations)
            && $user_organization = Yii::$app->user->getIdentity()->getOrganization() ?? null
        ) {
            $this->_nested_organisations = [$user_organization->id => $user_organization];

            $items = [];
            foreach (Organizations::find()->all() as $model) {
                $items[] = (object)[
                    'id' => $model->id,
                    'parent_id' => $model->parent_id, //
                    'model' => $model,
                ];
            }

            $pointers = Tree::getPointers($items);
            $this->_nested_organisations = Tree::getNestedChildren([$pointers[$user_organization->id]] ?? null, true);
        }

        return $this->_nested_organisations;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialists()
    {
        return $this->hasMany(Specialists::class, ['id_user' => 'id']);
    }

    /**
     * Проверяет 3 неудачные попытки авторизации за последние 30 минут
     * @return bool
     */
    public function checkLoginAttempts()
    {
        $settings = SecuritySettings::findOne(['id' => SecuritySettings::DEFAULT_ID]);
        $attemptCount = 3;

        $date = new \DateTime();
        $date->sub(new \DateInterval('PT30M'));
        $count = (new \yii\db\Query())
            ->from('login_attempts')
            ->where(['>', 'date', $date->format('Y-m-d H:i:s')])
            ->andWhere(['id_user' => $this->id])
            ->count();
        if ($count < $attemptCount) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохраняет неудачную попытку логина
     */
    public function addLoginAttempt()
    {
        \Yii::$app->db->createCommand()->insert('login_attempts', [
            'id_user' => $this->id,
            'date' => new Expression('NOW()'),
        ])->execute();
    }

    /**
     * Блокирует пользователя на 30 минут
     */
    public function blockUntil()
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT30M'));
        $this->block_until = $date->format('Y-m-d H:i:s');
        $this->save(false);
        // очистить попытки
        $this->clearLoginAttempts();
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
     * Блокирует пользователя на 30 минут
     */
    public function block()
    {
        $this->is_blocked = true;
        return $this->save();
    }

    /**
     * @param string $login
     * @return \app\common\models\UserModel|\app\modules\v1\models\UserResource
     */
    public static function findByLogin($login)
    {
        $login = str_replace(['_', '%'], ['\_', '\%'], $login);

        return static::find()
            ->where(['ilike', 'login', $login, false])
            ->one();
    }

    public static function findBySudirUid($sudirUid)
    {
        return static::find()
            ->where(['sudir_uid' => $sudirUid])
            ->one();
    }

    /**
     * @return int
     */
    public function getId_specialist()
    {
        return $this->_id_specialist;
    }

    /**
     * @param int $id_specialist
     */
    public function setId_specialist($id_specialist)
    {
        $this->_id_specialist = $id_specialist;
    }

    /**
     * @return array
     */
    public function organizationOptions()
    {
        $options = [];
        if (count($this->specialists) > 1) {
            foreach ($this->specialists as $specialist) {
                if ($specialist->organization !== null && !$specialist->isExpelledAtDate()) {
                    $options[] = [
                        'id_organization' => $specialist->organization->id,
                        'short_name' => $specialist->organization->short_name,
                        'name' => $specialist->organization->name,
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * @param bool   $result
     * @param string $dateTo
     */
    protected function logBlockUntil($result, $dateTo)
    {
        UserLogManager::autoAuthErrorApiUserBlock($this, $result, $dateTo);
    }

//    /**
//     * @return bool
//     */
//    public function validatePassword($password)
//    {
//        return \Yii::$app->security->validatePassword($password, $this->password);
//    }

//    /**
//     * @param $password
//     * @throws \yii\base\Exception
//     */
//    public function setPassword($password)
//    {
//        $this->password = \Yii::$app->security->generatePasswordHash($password);
//    }

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

    public function clearLoginAttempts()
    {
        \Yii::$app->db->createCommand()->delete('login_attempts', [
            'id_user' => $this->id,
        ])->execute();
    }
}
