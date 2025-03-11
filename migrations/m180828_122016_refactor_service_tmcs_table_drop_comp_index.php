<?php

use app\commands\migrate\Migration;

/**
 * Class m180828_122016_refactor_service_tmcs_table_drop_comp_index
 */
class m180828_122016_refactor_service_tmcs_table_drop_comp_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx-service_tmcs-unique_tmc_class', 'service_tmcs');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createIndex(
            'idx-service_tmcs-unique_tmc_class',
            'service_tmcs',
            ['id_service', 'tmc_class'],
            true
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180828_122016_refactor_service_tmcs_table_drop_comp_index cannot be reverted.\n";

        return false;
    }
    */
}
