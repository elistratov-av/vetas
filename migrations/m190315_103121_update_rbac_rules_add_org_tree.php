<?php

use app\commands\migrate\Migration;

/**
 * Class m190315_103121_update_rbac_rules_add_org_tree
 */
class m190315_103121_update_rbac_rules_add_org_tree extends Migration
{
    private static $rules = [
        \app\common\components\rbac\rules\UserOrgTreeRule::class,
        \app\common\components\rbac\rules\AllOrgsCompositeRule4::class,
    ];

    private static $permissionNames = [
        'admin.users.manage',
        'data.specialists.manage',
        'registry.schedule.manage',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$rules as $className) {
            $rule = new $className();
            $auth->add($rule);
        }

        foreach (self::$permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = 'AllOrgsCompositeRule4';
            $auth->update($permissionName, $permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$rules as $className) {
            $rule = new $className();
            $auth->remove($rule);
        }

        foreach (self::$permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = null;
            $auth->update($permissionName, $permission);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190315_103121_update_rbac_rules_add_org_tree cannot be reverted.\n";

        return false;
    }
    */
}
