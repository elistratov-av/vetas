<?php

namespace app\modules\v2\modules\user\models;

use app\common\models\UserModel;
use app\modules\adminfstek\helpers\PasswordHelper;
use app\modules\adminfstek\traits\PasswordTrait;
use Yii;
use yii\base\Model;

/**
 * Class ChangePasswordModel
 * @package app\modules\v2\modules\user\models
 */
class ChangePasswordModel extends Model
{
    use PasswordTrait;

    /**
     * @var string
     */
    public $old_password;
    /**
     * @var string
     */
    public $new_password;
    /**
     * @var UserModel
     */
    private $user;

    /**
     * @return UserModel
     */
    public function getUser(): UserModel
    {
        return $this->user;
    }

    /**
     * @param UserModel $user
     */
    public function setUser(UserModel $user): void
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_password', 'new_password'], 'trim'],
            [['old_password', 'new_password'], 'required'],
            ['old_password', 'string'],
            ['new_password', 'string', 'min' => 6],
            ['new_password', 'match', 'pattern' => PasswordHelper::pattern(), 'message' => PasswordHelper::errorMessage()],
            ['old_password', 'validatePassword', 'skipOnError' => true],
        ];
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validatePassword($attribute, $params, $validator)
    {
        if (!$this->user->validatePassword($this->$attribute)) {
            $this->addError($attribute, 'Неверно указан текущий пароль пользователя');
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function changePassword()
    {
        if (!$this->validate()) {
            return false;
        }

        if (!$this->user->setPassword($this->new_password)) {
            $this->addError('new_password', $this->user->getFirstError('password'));

            return false;
        }
        $this->user->setAuthKey();

        $result = $this->user->update(true, ['password', 'auth_key', 'is_temp_password', 'password_valid_till', 'password_valid_till_min', 'updated_at', 'updated_by']);

        if ($result !== false) {
            $this->savePasswordHistory($this->user);
        }

        return $result;
    }
}
