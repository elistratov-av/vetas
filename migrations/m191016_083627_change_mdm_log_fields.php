<?php

use app\commands\migrate\Migration;

/**
 * Class m191016_083627_change_mdm_log_fields
 */
class m191016_083627_change_mdm_log_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('mdm.log', 'xml');
        $this->addColumn('mdm.log', 'data', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191016_083627_change_mdm_log_fields cannot be reverted.\n";

        return false;
    }

}
