<?php

use app\commands\migrate\Migration;

/**
 * Class m190305_142439_add_rbac_rules_3
 */
class m190305_142439_add_rbac_rules_3 extends Migration
{
    private static $rules = [
        \app\common\components\rbac\rules\UserRootOrgRule::class,
        \app\common\components\rbac\rules\AllOrgsCompositeRule3::class,
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
        echo "m190305_142439_add_rbac_rules_3 cannot be reverted.\n";

        return false;
    }
    */
}
