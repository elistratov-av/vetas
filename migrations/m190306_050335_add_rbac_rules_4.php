<?php

use app\commands\migrate\Migration;

/**
 * Class m190306_050335_add_rbac_rules_4
 */
class m190306_050335_add_rbac_rules_4 extends Migration
{
    private static $permissionNames = [
        'activity.visits.services-edit',
        'activity.visits.tmc',
        'activity.visits.descriptions',
        'activity.visits.finish',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        $rule = new \app\common\components\rbac\rules\VisitSpecialistRule();
        $auth->add($rule);

        foreach (self::$permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = $rule->name;
            $auth->update($permissionName, $permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = null;
            $auth->update($permissionName, $permission);
        }

        $rule = new \app\common\components\rbac\rules\VisitSpecialistRule();
        $auth->remove($rule);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190306_050335_add_rbac_rules_4 cannot be reverted.\n";

        return false;
    }
    */
}
