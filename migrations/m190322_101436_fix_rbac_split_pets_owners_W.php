<?php

use app\commands\migrate\Migration;

/**
 * Class m190322_101436_fix_rbac_split_pets_owners_W
 */
class m190322_101436_fix_rbac_split_pets_owners_W extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        $parentRole = $auth->getRole(\app\common\components\rbac\Role::ROLE_VET_SPECIALIST_GOS);
        $role = $auth->getRole(\app\common\components\rbac\Role::ROLE_VET_SPECIALIST_GOS_AMB);

        $auth->removeChild($parentRole, $role);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190322_101436_fix_rbac_split_pets_owners_W cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190322_101436_fix_rbac_split_pets_owners_W cannot be reverted.\n";

        return false;
    }
    */
}
