<?php

namespace app\common\components\rbac\rules;

/**
 * Class UserRootOrgRule
 * @package app\common\components\rbac\rules
 *
 * Пользователь является пользователем головной организации
 */
class UserRootOrgRule extends UserOrgRule
{
    /**
     * @var string
     */
    public $name = 'UserRootOrgRule';

    /**
     * @param \app\common\models\UserModel $user
     * @param \yii\rbac\Item               $item   the role or permission that this rule is associated with
     * @param array                        $params параметры, переданные в ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if ($user->specialist === null || empty($user->specialist->id_organization) || $user->specialist->organization === null) {
            return false;
        }

        return $user->specialist->organization->isRoot();
    }
}
