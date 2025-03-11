<?php

namespace app\modules\adminfstek\models\forms;

use app\modules\adminfstek\helpers\PasswordHelper;
use app\modules\adminfstek\traits\PasswordTrait;
use Yii;
use yii\base\Model;

/**
 * Class UserPasswordChangeForm
 * @package app\modules\adminfstek\models\forms
 *
 * @property \app\common\models\UserModel $user
 */
class UserPasswordChangeForm extends Model
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
     * @var string
     */
    public $new_password_repeat;

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
    public function setUser($user)
    {
        $this->_user = $user;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['old_password', 'new_password', 'new_password_repeat'], 'required'],
            ['new_password_repeat', 'compare', 'compareAttribute' => 'new_password'],
            ['new_password', 'string', 'min' => 6],
            ['new_password', 'match', 'pattern' => PasswordHelper::pattern(), 'message' => PasswordHelper::errorMessage()],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'old_password' => Yii::t('app', 'Старый пароль'),
            'new_password' => Yii::t('app', 'Новый пароль'),
            'new_password_repeat' => Yii::t('app', 'Подтверждение пароля'),
        ];
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
        if (!$this->user->validatePassword($this->old_password)) {
            $this->addError('old_password', 'Неправильный пароль');
            return false;
        }

        $user = $this->_user;
        if (!$user->setPassword($this->new_password)) {
            $this->addError('new_password', $this->user->getFirstError('password'));

            return false;
        }
        $user->setAuthKey();

        $result = $user->save(true, ['password', 'auth_key', 'is_temp_password', 'password_valid_till', 'password_valid_till_min', 'updated_at', 'updated_by']);

        if ($result !== false) {
            $this->savePasswordHistory($user);
        }

        return $result;
    }
}
