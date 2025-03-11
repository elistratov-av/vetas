<?php

namespace app\modules\adminfstek\models\forms;

use app\models\db\admin\AdminUser;
use app\modules\adminfstek\components\UserLogManager;
use app\modules\adminfstek\traits\PasswordTrait;
use yii\base\Model;

/**
 * Class AdminUserForm
 * @package app\modules\adminfstek\models\forms
 *
 * @property \app\models\db\admin\AdminUser $user
 */
class AdminUserForm extends Model
{
    use PasswordTrait;

    const SCENARIO_UPDATE = 'update';
    const SCENARIO_BLOCK_UNBLOCK = 'block_unblock';

    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $f_fio;
    /**
     * @var string
     */
    public $i_fio;
    /**
     * @var string
     */
    public $o_fio;
    /**
     * @var int
     */
    public $role;
    /**
     * @var int
     */
    public $is_blocked;
    /**
     * @var bool
     */
    public $temp_block;

    /**
     * @var \app\models\db\admin\AdminUser
     */
    private $_user;

    /**
     * @return \app\models\db\admin\AdminUser
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param \app\models\db\admin\AdminUser $user
     */
    public function setUser($user): void
    {
        $this->_user = $user;

        $this->load($user->attributes, '');

        if ($this->_user !== null && $this->_user->block_until > date("Y-m-d H:i:s")) {
            $this->temp_block = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return
        [
            [['login', 'role', 'email', 'f_fio', 'i_fio'], 'required', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['login', 'f_fio', 'i_fio', 'o_fio'], 'filter', 'filter' => 'trim', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['login', 'f_fio', 'i_fio', 'o_fio'], 'filter', 'filter' => 'strip_tags', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['login', 'f_fio', 'i_fio', 'o_fio'], 'string', 'min' => 2, 'max' => 255, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            ['role', 'in', 'range' => AdminUser::roles(), 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['is_blocked'], 'boolean', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE, self::SCENARIO_BLOCK_UNBLOCK]],
            [['is_blocked'], 'default', 'value' => false, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE, self::SCENARIO_BLOCK_UNBLOCK]],
            ['email', 'email'],
            ['temp_block', 'boolean'],
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
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function createUser()
    {
        if ($this->load(\Yii::$app->request->post()) && $this->validate()) {
            $attributes = $this->attributes;
            unset($attributes['temp_block']);
            $this->_user->load($attributes, '');
            $password = $this->generatePassword();
            $this->_user->setPassword($password, true);
            $this->_user->setAuthKey();

            $result = $this->_user->save();
            UserLogManager::createAdminUser($this->_user, $result);
            UserLogManager::createAdminUserAccess($this->_user, $result);
            if ($this->_user->hasErrors()) {
                $this->addErrors($this->_user->getErrors());
            } else {
                $this->_user->refresh();
                \Yii::$app->session->setFlash('success', 'Пользователь успешно создан');
                if ($this->sendPassword($this->_user->email, $password)) {
                    \Yii::$app->session->addFlash('success', 'Временный пароль отправлен на email пользователя');
                }
            }

            return $result;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function updateUser()
    {
        $this->scenario = self::SCENARIO_UPDATE;

        if ($this->load(\Yii::$app->request->post()) && $this->validate()) {
            $attributes = $this->attributes;
            unset($attributes['temp_block']);
            $old_is_blocked = $this->_user->is_blocked;
            $old_block_until = $this->_user->block_until;
            $this->_user->load($attributes, '');
            if ($this->temp_block != 1) {
                $this->_user->block_until = null;
            }

            $clone = clone($this->_user);
            $result = $this->_user->save();
            UserLogManager::changeAdminUser($clone, $result);
            UserLogManager::changeAdminUserAccess($clone, $result);
            if ($this->_user->hasErrors()) {
                $this->addErrors($this->_user->getErrors());
            } else {
                $this->_user->refresh();
                if (empty($old_is_blocked) && $this->_user->is_blocked === true) {
                    UserLogManager::manualAdminUserBlock($this->_user, $result);
                } elseif ($old_is_blocked === true && $this->_user->is_blocked === false) {
                    UserLogManager::manualAdminUserUnblock($this->_user, $result);
                } elseif (!empty($old_block_until) && $this->_user->block_until === null) {
                    UserLogManager::manualAdminUserUnblock($this->_user, $result);
                }
            }

            return $result;
        }

        return false;
    }
}
