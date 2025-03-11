<?php

namespace app\modules\admin\models;

use DateTime;
use Yii;
use yii\base\Model;


/**
 * Class LoginForm
 * @package app\modules\admin\models
 */
class LoginForm extends Model
{
    public $login;
    public $password;
    public $last_login;
    public $ip;
    public $rememberMe = true;
    private $_user;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            ['last_login', 'integer'],
            ];
    }

    /**
     * @param $attribute
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getAdmin();

            if ($user === null || !$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный логин или пароль');
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getAdmin();
            $user->last_login = (new \DateTime())->format(DateTime::ISO8601);
            $user->ip = $_SERVER['REMOTE_ADDR'];
            $user->save(false);
            return Yii::$app->user->login($user, $this->rememberMe ? 300 : 0);
        }
        return false;
    }

    /**
     * @return Admin
     */
    protected function getAdmin()
    {
        if ($this->_user === null) {
            $this->_user = Admin::findByUsername($this->login);
        }
        return $this->_user;


    }
}