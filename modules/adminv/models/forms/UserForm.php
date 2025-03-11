<?php

namespace app\modules\adminv\models\forms;

use app\common\validators\FioValidator;
use yii\base\Model;

/**
 * Class UserForm
 * @package app\modules\adminv\models\forms
 *
 * @property \app\common\models\UserModel $user
 */
class UserForm extends Model
{
    const SCENARIO_UPDATE = 'update';

    public $login;
    public $password;
    public $passwordRepeat;
    public $f_fio;
    public $i_fio;
    public $o_fio;
    public $birthday;
    public $sex;

    public $is_blocked;
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
            [['login','password', 'passwordRepeat'], 'required', 'on' => [self::SCENARIO_DEFAULT]],
            ['password', 'string', 'min' => 6, 'on' => [self::SCENARIO_DEFAULT]],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'on' => [self::SCENARIO_DEFAULT]],
            [['login', 'f_fio', 'i_fio', 'o_fio'], 'filter', 'filter' => 'trim', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['f_fio', 'i_fio', 'sex', 'birthday'], 'required', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['login'], 'string', 'max' => 255, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['f_fio'], 'string', 'max' => 150, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['i_fio', 'o_fio'], 'string', 'max' => 50, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['f_fio', 'i_fio', 'o_fio'], FioValidator::class, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['birthday'], 'date', 'format' => 'php:Y-m-d', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE]],
            [['is_blocked', 'temp_block'], 'safe'],
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
            unset($attributes['password']);
            unset($attributes['passwordRepeat']);
            unset($attributes['temp_block']);
            $this->_user->load($attributes, '');
            $this->_user->setPassword($this->password);
            $this->_user->setAuthKey();

            $result = $this->_user->save();
            if ($this->_user->hasErrors()) {
                $this->addErrors($this->_user->getErrors());
            } else {
                $this->_user->refresh();
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
            unset($attributes['password']);
            unset($attributes['passwordRepeat']);
            unset($attributes['temp_block']);
            $this->_user->load($attributes, '');
            if ($this->temp_block != 1) {
                $this->_user->block_until = null;
            }

            $result = $this->_user->save();
            if ($this->_user->hasErrors()) {
                $this->addErrors($this->_user->getErrors());
            } else {
                $this->_user->refresh();
            }

            return $result;
        }

        return false;
    }
}
