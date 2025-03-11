<?php

namespace app\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * Class UserPasswordChangeForm
 * @package app\modules\admin\models\forms
 *
 * @property \app\common\models\UserModel $user
 */
class UserPasswordChangeForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $newPasswordRepeat;

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
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['newPassword', 'newPasswordRepeat'], 'required'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword'],
            ['newPassword', 'string', 'min' => 6],
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
            $user->setAuthKey();
            return $user->save();
        } else {
            return false;
        }
    }
}
