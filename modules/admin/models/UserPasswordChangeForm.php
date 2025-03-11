<?php

namespace app\modules\admin\models;


use Yii;
use yii\base\Model;

/**
 * Class UserPasswordChangeForm
 * @package app\modules\admin\models
 */
class UserPasswordChangeForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $newPasswordRepeat;
    private $_user;

    /**
     * UserPasswordChangeForm constructor.
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['newPassword', 'newPasswordRepeat'], 'required'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword'],
            ['newPassword', 'string', 'min' => 4],
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
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function changePassword()
    {
        if ($this->validate()) {
            $user = $this->_user;
            $user->setPassword($this->newPassword);
            return $user->save();
        } else {
            return false;
        }
    }
}
