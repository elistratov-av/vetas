<?php

use app\commands\migrate\Migration;

/**
 * Class m190425_112230_remove_remaining_tmc_types
 */
class m190425_112230_remove_remaining_tmc_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $permissionNames = [
            'data.classificators.tmc_types.menu',
            'data.classificators.tmc_types',
            'data.classificators.tmc_types.W',
        ];

        $this->db
            ->createCommand()
            ->delete(
                'auth_item_child',
                [
                    'or',
                    ['in', 'parent', $permissionNames],
                    ['in', 'child', $permissionNames],
                ]
            )
            ->execute();

        $this->db
            ->createCommand()
            ->delete(
                'auth_item',
                ['in', 'name', $permissionNames]
            )
            ->execute();

        $this->dropTable('diseases_tmc_types');
        $this->dropTable('tmc_types');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190425_112230_remove_remaining_tmc_types cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190425_112230_remove_remaining_tmc_types cannot be reverted.\n";

        return false;
    }
    */
}
