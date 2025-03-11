<?php

use yii\db\Migration;

/**
 * Handles the creation of table `etp_message`.
 */
class m180809_115100_create_etp_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE SCHEMA etp");

        $this->createTable('etp.message', [
            'id' => $this->primaryKey(),
            'visit_id' => $this->integer()->notNull(),
            'service_number' => $this->string(),
            'message' => $this->json(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('etp.message');
        $this->execute("DROP SCHEMA etp");
    }
}
