<?php

use app\commands\migrate\Migration;

/**
 * Class m190304_093849_add_rbac_rules
 */
class m190304_093849_add_rbac_rules extends Migration
{
    private static $rules = [
        \app\common\components\rbac\rules\UserOrgRule::class,
        \app\common\components\rbac\rules\UserAllOrgsRule::class,
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
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190304_093849_add_rbac_rules cannot be reverted.\n";

        return false;
    }
    */
}
