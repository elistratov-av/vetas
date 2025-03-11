<?php

namespace app\modules\adminv\models\forms;

use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\models\db\Specialists;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class LoginForm
 * @package app\modules\adminv\models
 *
 * @property \app\common\models\UserModel $user
 */
class LoginForm extends Model
{
    const SCENARIO_SELECT_ORGANIZATION = 'select_organization';

    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $password;
    /**
     * @var bool
     */
    public $rememberMe = true;
    /**
     * @var bool
     */
    public $requireSelectOrganization = false;
    /**
     * @var array
     */
    public $organizations;
    /**
     * @var int
     */
    public $id_organization;

    /**
     * @var \app\common\models\UserModel
     */
    private $_user;


    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if ($this->scenario == self::SCENARIO_SELECT_ORGANIZATION) {
            $this->login = \Yii::$app->session->get('__login');
            $this->organizations = \Yii::$app->session->get('__organizations');
        }
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['login', 'password'], 'required', 'message' => ''],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            ['id_organization', 'integer', 'on' => [self::SCENARIO_SELECT_ORGANIZATION]],
        ];
    }

    /**
     * @param $attribute
     */
    public function validatePassword($attribute)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null) {
            $this->addErrors(array_fill_keys(['login', 'password'], 'Неверный логин или пароль'));
            return;
        }
        if ($user->is_blocked) {
            $this->addErrors(array_fill_keys(['login', 'password'], 'Ваша учетная запись заблокирована'));
            return;
        }
        if ($user->isBlockedUntil()) {
            $this->addErrors(array_fill_keys(['login', 'password'], 'Ваша учетная запись заблокирована до ' . \DateTime::createFromFormat('Y-m-d H:i:s', $user->block_until)->format('H:i:s d.m.Y')));
            return;
        }
        if (!$user->validatePassword($this->password)) {
            $this->addErrors(array_fill_keys(['login', 'password'], 'Неверный логин или пароль'));
            $user->addLoginAttempt();
            if (!$user->checkLoginAttempts()) {
                $user->blockUntil();
                $this->addErrors(array_fill_keys(['login', 'password'], 'Ваша учетная запись заблокирована на 30 минут'));
            }
            return;
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if (empty($user->specialists)) {
                $this->addErrors(array_fill_keys(['login', 'password'], 'Недостаточно прав доступа'));
                return false;
            }

            $specialists = $user->specialists;
            $organizations = [];

            $roleNames = [
                Role::ROLE_SYSADMIN_GOS,
                Role::ROLE_MANAGEMENT_GOS,
                Role::ROLE_SHELTER_MANAGEMENT,
            ];

            /* @var $auth \app\common\components\rbac\DbManager */
            $auth = \Yii::$app->authManager;

            foreach ($specialists as $i => $specialist) {
                if ($specialist->organization === null || $specialist->isExpelledAtDate()) {
                    unset($specialists[$i]);
                    continue;
                }
                $found = false;
                foreach ($roleNames as $roleName) {
                    $role = $auth->getAssignment($roleName, $user->id, $specialist->id);
                    if ($role !== null) {
                        $found = true;
                        $organizations[$specialist->id_organization] = $specialist->organization->short_name;
                        unset($role);
                    }
                }
                if ($found === false) {
                    unset($specialists[$i]);
                }
            }

            if (empty($specialists)) {
                $this->addErrors(array_fill_keys(['login', 'password'], 'Недостаточно прав доступа'));
                return false;
            }

            if (count($organizations) > 1) {
                $this->requireSelectOrganization = true;
                \Yii::$app->session->set('__organizations', $organizations);
                \Yii::$app->session->set('__login', $this->login);

                return false;
            }

            $specialist = reset($specialists);
            $user->setId_specialist($specialist->id);
            \Yii::$app->session->set('__id_specialist', $specialist->id);

            if (\Yii::$app->user->login($user, $this->rememberMe ? ArrayHelper::getValue(\Yii::$app->params, 'jwt_token_ttl', 86400) : 0)) {
                $user->updateAttributes([
                    'last_login' => (new \DateTime())->format(\DateTime::ISO8601),
                ]);

                return true;
            }
        }

        $this->addErrors(array_fill_keys(['login', 'password'], 'Недостаточно прав доступа'));

        return false;
    }

    /**
     * @return \app\common\models\UserModel
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = UserModel::findByLogin($this->login);
        }

        return $this->_user;
    }

    /**
     * @return bool
     */
    public function selectOrganization()
    {
        if (empty($this->login)
            || empty($this->organizations)
            || !is_array($this->organizations)
            || empty($this->id_organization)
            || !array_key_exists($this->id_organization, $this->organizations)) {
            return false;
        }

        $user = $this->getUser();
        if ($user === null) {
            return false;
        }

        $specialist = Specialists::findOne([
            'id_user' => $this->user->id,
            'id_organization' => $this->id_organization,
        ]);

        if ($specialist === null) {
            return false;
        }
        if (!empty($specialist->expel_date) && $specialist->isExpelledAtDate()) {
            return false;
        }

        $roleNames = [
            Role::ROLE_SYSADMIN_GOS,
            Role::ROLE_MANAGEMENT_GOS,
            Role::ROLE_SHELTER_MANAGEMENT,
        ];

        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->authManager;

        $roles = $auth->getRolesByUser($this->user->id, $specialist->id);
        if (empty($roles)) {
            return false;
        }
        $found = false;
        foreach ($roles as $role) {
            $found = in_array($role->name, $roleNames, true);
            if ($found === true) {
                break;
            }
        }
        if ($found === false) {
            return false;
        }

        $user->setId_specialist($specialist->id);
        \Yii::$app->session->set('__id_specialist', $specialist->id);

        if (\Yii::$app->user->login($user, $this->rememberMe ? 300 : 0)) {
            $user->updateAttributes([
                'last_login' => (new \DateTime())->format(\DateTime::ISO8601),
            ]);

            return true;
        }

        return false;
    }
}
