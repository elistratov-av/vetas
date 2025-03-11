<?php

namespace app\modules\admin\models;

use yii\base\Model;
use Yii;

/**
 * Class PasswordChangeForm
 * @package app\modules\admin\models
 */
class PasswordChangeForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $newPasswordRepeat;
    private $_user;

    /**
     * PasswordChangeForm constructor.
     * @param Admin $admin
     * @param array $config
     */
    public function __construct(Admin $admin, $config = [])
    {
        $this->_user = $admin;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            ['currentPassword', 'currentPassword'],
            ['newPassword', 'string', 'min' => 8],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'newPassword' => 'Новый пароль',
            'newPasswordRepeat' => 'Подтверждение пароля',
            'currentPassword' => 'Текущий пароль',
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function currentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->_user->validatePassword($this->$attribute)) {
                $this->addError($attribute, 'Неверный пароль');
            }
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function changePassword()
    {
        if ($this->validate()) {
            $admin = $this->_user;
            $admin->setPassword($this->newPassword);
            return $admin->save();
        } else {
            return false;
        }
    }
}
