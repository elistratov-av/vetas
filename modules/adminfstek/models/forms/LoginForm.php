<?php

namespace app\modules\adminfstek\models\forms;

use app\models\db\admin\AdminUser;
use app\modules\adminfstek\traits\PasswordTrait;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class LoginForm
 * @package app\modules\adminfstek\models
 */
class LoginForm extends Model
{
    use PasswordTrait;

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
     * @var AdminUser
     */
    private $user;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['login', 'password'], 'required', 'message' => ''],
            [['login', 'password'], 'string'],
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function login()
    {
        if ($this->validate()) {
            $this->findUser();
            if ($this->user === null) {
                $this->addErrors(array_fill_keys(['login', 'password'], 'Введен неверный логин или пароль'));
                return false;
            }
            if (!$this->user->validatePassword($this->password)) {
                $this->addErrors(array_fill_keys(['login', 'password'], 'Введен неверный логин или пароль'));
                $this->user->addLoginAttempt();
                if (!$this->user->checkLoginAttempts()) {
                    $this->user->blockUntil();
                    $this->addErrors(array_fill_keys(['login', 'password'], 'Ваша учетная запись заблокирована на 30 минут'));
                }
                return false;
            }
            if ($this->user->is_blocked == AdminUser::STATUS_BLOCKED) {
                $this->addErrors(array_fill_keys(['login', 'password'], 'Ваша учетная запись заблокирована'));
                return false;
            }
            if ($this->user->isBlockedUntil()) {
                $this->addErrors(array_fill_keys(['login', 'password'], 'Ваша учетная запись заблокирована до ' . \DateTime::createFromFormat('Y-m-d H:i:s', $this->user->block_until)->format('H:i:s d.m.Y')));
                return false;
            }
            $this->user->last_login = (new \DateTime())->format(\DateTime::ISO8601);
            $this->user->ip = \Yii::$app->request->getUserIP();
            $this->user->save(false);

            return Yii::$app->user->login($this->user, $this->rememberMe ? ArrayHelper::getValue(\Yii::$app->params, 'jwt_token_ttl', 86400) : 0);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function needsChangePassword()
    {
        return $this->user->needsChangePassword();
    }

    /**
     * @return void
     */
    private function findUser()
    {
        $user = AdminUser::findByUsername($this->login);
        if ($user !== null) {
            $this->user = $user;
        }
    }

    /**
     * @return \app\models\db\admin\AdminUser|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
