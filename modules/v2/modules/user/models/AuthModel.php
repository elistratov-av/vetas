<?php

namespace app\modules\v2\modules\user\models;

use app\models\db\AuthItem2;

class AuthModel
{
    /**
     * @return AuthItem2[]
     * @throws \Throwable
     */
    public function roles()
    {
        return AuthItem2::find()
            ->select(['name', 'description'])
            ->where(['type' => 1])
            ->orderBy('name ASC')
            ->all();
    }

}
