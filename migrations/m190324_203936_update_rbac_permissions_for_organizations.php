<?php

use app\commands\migrate\Migration;

/**
 * Class m190324_203936_update_rbac_permissions_for_organizations
 */
class m190324_203936_update_rbac_permissions_for_organizations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db
            ->createCommand()
            ->update('auth_item', ['rule_name' => 'AllOrgsCompositeRule1'], ['name' => 'data.organizations.manage.W'])
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->db
            ->createCommand()
            ->update('auth_item', ['rule_name' => null], ['name' => 'data.organizations.manage.W'])
            ->execute();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190324_203936_update_rbac_permissions_for_organizations cannot be reverted.\n";

        return false;
    }
    */
}
