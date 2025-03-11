<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%duplicates_groups}}`.
 */
class m240828_090150_create_duplicates_groups_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%duplicates_groups}}', [
            'id' => $this->primaryKey(),
            'duplicates' => 'integer[]',
            'type' => $this->string(10),
            'status' => $this->string(64),
            'comment' => $this->string(64),
            'created_at' => $this->dateTime()->defaultExpression("timezone('Europe/Moscow', now())"),
            'updated_at' => $this->dateTime()->defaultExpression("timezone('Europe/Moscow', now())")
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%duplicates_groups}}');
    }
}
