<?php

namespace app\modules\adminfstek\models\forms;

use app\modules\adminfstek\components\UserLogManager;
use app\modules\adminfstek\traits\PasswordTrait;
use yii\base\Model;

/**
 * Class UserForm
 * @package app\modules\adminfstek\models\forms
 *
 * @property \app\common\models\UserModel $user
 */
class UserForm extends Model
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
     * @var
     */
    public $birthday;
    /**
     * @var
     */
    public $sex;
    /**
     * @var
     */
    public $sudir_uid;

    /**
     * @var
     */
    public $is_blocked;
    /**
     * @var
     */
    public $temp_block;

    /**
     * @var \app\common\models\UserModel
     */
    private $_user;

    /**
     * @return \app\common\models\UserModel
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param \app\common\models\UserModel $user
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
            [['login','email', 'f_fio', 'i_fio', 'sex', 'birthday'], 'required', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['login', 'f_fio', 'i_fio', 'o_fio', 'sudir_uid'], 'filter', 'filter' => 'trim', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['login', 'f_fio', 'i_fio', 'o_fio', 'sudir_uid'], 'filter', 'filter' => 'strip_tags', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['login', 'f_fio', 'i_fio', 'o_fio', 'sudir_uid'], 'string', 'min' => 2, 'max' => 255, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            ['email', 'email', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['birthday'], 'date', 'format' => 'php:Y-m-d', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['is_blocked', 'temp_block'], 'safe', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE, self::SCENARIO_BLOCK_UNBLOCK]],
            [['sudir_uid'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'login' => 'Логин',
            'password' => 'Пароль',
            'passwordRepeat' => 'Повторите пароль',
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
            'sudir_uid' => 'СУДИР ИД',
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
            UserLogManager::createApiUser($this->_user, $result);
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
            UserLogManager::changeApiUser($clone, $result);
            if ($this->_user->hasErrors()) {
                $this->addErrors($this->_user->getErrors());
            } else {
                $this->_user->refresh();
                if (empty($old_is_blocked) && $this->_user->is_blocked === true) {
                    UserLogManager::manualApiUserBlock($this->_user, $result);
                } elseif ($old_is_blocked === true && $this->_user->is_blocked === false) {
                    UserLogManager::manualApiUserUnblock($this->_user, $result);
                } elseif (!empty($old_block_until) && $this->_user->block_until === null) {
                    UserLogManager::manualApiUserUnblock($this->_user, $result);
                }
            }

            return $result;
        }

        return false;
    }
}
