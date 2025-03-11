<?php

namespace app\common\components\rbac\rules;

use app\models\db\Organizations;

/**
 * Class UserOrgTreeRule
 * @package app\common\components\rbac\rules
 *
 * Пользователям доступны данные, связанные с сетью организаций, к кторой относится организация пользователя.
 */
class UserOrgTreeRule extends UserOrgRule
{
    /**
     * @var string
     */
    public $name = 'UserOrgTreeRule';

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

        $id_organization = $this->extractIdOrganization($params);

        if ($id_organization === null) {
            return false;
        }

        $ids = $this->organizationTreeIds($user);

        if (is_array($id_organization)) {
            $diff = array_intersect($id_organization, $ids);
            return count($diff) == count($id_organization);
        }

        return in_array($id_organization, $ids);
    }

    /**
     * @param \app\common\models\UserModel $user
     * @return array
     */
    public function organizationTreeIds($user)
    {
        return Organizations::orgTreeIds($user->specialist->id_organization);
    }
}
