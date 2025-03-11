<?php

use yii\db\Migration;

/**
 * Class m180918_093524_add_etp_status_log_table
 */
class m180918_093524_add_etp_status_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('etp.status_log', [
            'id' => $this->bigPrimaryKey(),
            'log_time' => $this->dateTime(),
            'service_number' => $this->string(),
            'visit_id' => $this->integer(),
            'etp_status' => $this->string(10),
            'status' => $this->string(10)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('etp.status_log');
    }
}
