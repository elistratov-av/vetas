<?php

use app\commands\migrate\Migration;

/**
 * Class m181019_065242_add_message_field_to_etp_status_log
 */
class m181019_065242_add_message_field_to_etp_status_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('etp.status_log', 'message', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('etp.status_log', 'message');
    }
}
