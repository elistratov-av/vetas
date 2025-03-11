<?php


namespace app\common\components\rbac\rules;

use app\common\components\rbac\Role;
use app\common\components\rbac\Rule;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class BalanceAddRule
 * @package app\common\components\rbac\rules
 *
 * Доступна ли пользователю постановка на баланс организации
 */
class BalanceAddRule extends Rule
{

    /**
     * @param \app\common\models\UserModel $user
     * @param \yii\rbac\Item               $item   the role or permission that this rule is associated with
     * @param array                        $params параметры, переданные в ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (\Yii::$app->user->can(Role::ROLE_TECHNIC_MTO) !== true) {
            return false;
        }

        if ($user->specialist === null || empty($user->specialist->id_organization)) {
            return false;
        }

        return (new Query())
            ->select('id')
            ->from('tmc.org_tree')
            ->where([
                'OR',
                new Expression('path[1] = :id_org'),
                new Expression('path[2] = :id_org'),
                new Expression('path[3] = :id_org'),
            ])
            ->addParams([
                ':id_org' => $user->specialist->id_organization,
            ])
            ->exists();
    }
}