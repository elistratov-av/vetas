<?php

use yii\db\Migration;

/**
 * Handles the creation of table `elk_conflicts`.
 */
class m191008_072720_create_elk_conflicts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('elk.conflicts', [
            'id' => $this->primaryKey(),
            'sso_id' => $this->string(),
            'ext_id' => $this->string(),
            'data' => $this->json(),
            'error' => $this->text()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('elk.conflicts');
    }
}
