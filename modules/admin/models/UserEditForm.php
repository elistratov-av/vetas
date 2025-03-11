<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;

/**
 * Class UserEditForm
 * @package app\modules\admin\models
 */
class UserEditForm extends Model
{
    private $_user;

    public $id;
    public $login;
    public $is_blocked;
    public $temp_block;
    public $block_until;

    /**
     * UserEditForm constructor.
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, $config = [])
    {

        $this->_user = $user;
        $this->id = $user->id;
        $this->login = $user->login;
        $this->is_blocked = $user->is_blocked;
        $this->block_until = $user->block_until;
        if($this->block_until != null && $this->block_until > date("Y-m-d H:i:s")) {
            $this->temp_block = true;
        }
        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function editUser()
    {
        $attr = Yii::$app->request->post();
        $user = $this->_user;
        $user->login = $attr['UserEditForm']['login'];
        $user->is_blocked = $attr['UserEditForm']['is_blocked'];
        if(isset($attr['UserEditForm']['temp_block']) && !$attr['UserEditForm']['temp_block']) {
            $user->block_until = null;
        }

        if($this->validate()) {
            return $user->save();
        } else {
            return false;
        }
    }
}