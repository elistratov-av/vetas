<?php

use app\common\migrate\RbacMigration;
use app\common\components\rbac\Role;

/**
 * Class m210402_084239_new_rbac_permissions
 */
class m210402_174100_update_rbac_role extends RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('auth_item', ['name' => 'technicMto'], "name = 'technic_mto'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('auth_item', ['name' => 'technic_mto'], "name = 'technicMto'");
    }

}
