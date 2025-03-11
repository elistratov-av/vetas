<?php

use app\commands\migrate\Migration;

/**
 * Class m190304_104216_add_rbac_rules_2
 */
class m190304_104216_add_rbac_rules_2 extends Migration
{
    private static $rules = [
        \app\common\components\rbac\rules\AllOrgsCompositeRule1::class => [
            'admin.users.manage',
            'admin.users.manage.W',
            'admin.users.block',
            'admin.users.block-temporary',
            'admin.users.change-password',
            'admin.users.monitor',
            'admin.rbac.manage',
            'admin.rbac.manage-users',
            'admin.rbac.manage-users.W',
            'data.specialists.manage.W',
            'activity.visits.manage',
        ],
        \app\common\components\rbac\rules\AllOrgsCompositeRule2::class => [
            'data.pricelist.services',
            'data.pricelist.services.W',
            'data.organizations.balance',
            'data.organizations.balance-tmc-disposal',
            'data.organizations.balance.W',
            'registry.schedule.manage',
            'registry.schedule.shifts',
            'registry.schedule.timesheets',
            'activity.visits.services-edit',
            'activity.visits.start',
            'activity.visits.tmc',
            'activity.visits.cancel',
            'activity.visits.confirm-payment',
            'activity.visits.descriptions',
            'activity.visits.edit',
            'activity.visits.finish',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$rules as $className => $permissionNames) {
            $rule = new $className();
            $auth->add($rule);
            foreach ($permissionNames as $permissionName) {
                $permission = $auth->getPermission($permissionName);
                $permission->ruleName = $rule->name;
                $auth->update($permissionName, $permission);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$rules as $className => $permissionNames) {
            foreach ($permissionNames as $permissionName) {
                $permission = $auth->getPermission($permissionName);
                $permission->ruleName = null;
                $auth->update($permissionName, $permission);
            }
            $rule = new $className();
            $auth->remove($rule);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190304_104216_add_rbac_rules_2 cannot be reverted.\n";

        return false;
    }
    */
}
