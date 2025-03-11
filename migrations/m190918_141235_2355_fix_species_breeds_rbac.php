<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190918_141235_2355_fix_species_breeds_rbac
 */
class m190918_141235_2355_fix_species_breeds_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db->createCommand()
            ->delete(
                'auth_item_child',
                [
                    'and',
                    ['in', 'child', ['data.classificators.breeds.W', 'data.classificators.species.W']],
                    ['!=', 'parent', Role::ROLE_SYSADMIN_GOS],
                ]
            )->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190918_141235_2355_fix_species_breeds_rbac cannot be reverted.\n";

        return false;
    }
}
