<?php

use app\commands\migrate\Migration;

/**
 * Class m200608_063356_update_audit_log_table
 */
class m200608_063356_update_audit_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('audit.log', 'action_id', $this->string(500));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('audit.log', 'action_id');
    }
}
