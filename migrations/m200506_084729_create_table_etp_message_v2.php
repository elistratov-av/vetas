<?php

use app\commands\migrate\Migration;

/**
 * Class m200506_084729_create_table_etp_message_v2
 */
class m200506_084729_create_table_etp_message_v2 extends Migration
{
    private $tableName = 'etp.message_v2';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'visit_id' => $this->integer()->notNull(),
            'service_number' => $this->string(),
            'message' => $this->json(),
            'last_name' => $this->string(),
            'first_name' => $this->string(),
            'middle_name' => $this->string(),
            'sso_id' => $this->string(),
            'phone' => $this->string(),
            'email' => $this->string(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
