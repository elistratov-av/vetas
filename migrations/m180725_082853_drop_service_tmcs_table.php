<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `service_tmcs`.
 */
class m180725_082853_drop_service_tmcs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->dropTable('service_tmcs');
        $this->execute("DROP TABLE IF EXISTS service_tmcs");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
