<?php

namespace app\common\components\rbac;

use app\common\models\UserModel;

/**
 * Class Rule
 * @package app\common\components\rbac
 */
abstract class Rule extends \yii\rbac\Rule
{
    /**
     * @param int $user_id
     * @return \app\common\models\UserModel|null
     */
    protected function findUser($user_id)
    {
        $user = UserModel::findOne(['id' => $user_id]);

        if ($user->specialist === null) {
            return null;
        }

        return $user;
    }
}
