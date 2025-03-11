<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%external_api_logs}}`.
 */
class m240826_134910_create_external_api_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%external_api_logs}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression("timezone('Europe/Moscow', now())"),
            'request_ip' => $this->string()->notNull(),
            'request_url' => $this->string()->notNull(),
            'request_type' => $this->string(10)->notNull(),
            'request_headers' => $this->text()->notNull(),
            'request_body' => $this->text()->notNull(),
            'response_body' => $this->text()->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%external_api_logs}}');
    }
}
