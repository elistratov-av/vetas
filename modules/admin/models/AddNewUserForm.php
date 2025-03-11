<?php

namespace app\modules\admin\models;

use DateTime;
use Yii;
use yii\base\Model;


/**
 * Class AddNewUserForm
 * @package app\modules\admin\models
 */
class AddNewUserForm extends Model
{
    public $login;
    public $password;
    public $passwordRepeat;
    private $_user;

    /**
     * AddNewUserForm constructor.
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;
        $this->login = $user->login;
        $this->password = $user->password;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return
        [
            [['login','password', 'passwordRepeat'], 'required'],
            ['password', 'string', 'min' => 8],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function addNewUser()
    {
        $attr = Yii::$app->request->post();
        $user = $this->_user;
        $user->login = $attr['AddNewUserForm']['login'];
        $user->password = $attr['AddNewUserForm']['password'];
        $user->created_at = (new \DateTime())->format(DateTime::ISO8601);
        if ($this->validate()) {
            $user = $this->_user;
            $user->setPassword($user->password);
            return $user->save();
        } else {
            return false;
        }
    }
}