<?php

namespace app\modules\adminfstek\components;

use app\models\db\admin\AdminUser;
use yii\rbac\CheckAccessInterface;

/**
 * Class AccessChecker
 * @package app\modules\adminfstek\components
 */
class AccessChecker implements CheckAccessInterface
{
    /**
     * @inheritDoc
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        $user = AdminUser::findOne(['id' => $userId]);

        if ($user === null || $user->is_blocked !== AdminUser::STATUS_NOT_BLOCKED) {
            return false;
        }

        return $user->role === $permissionName;
    }
}
