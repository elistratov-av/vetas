<?php

use app\commands\migrate\Migration;

/**
 * Class m191127_132948_create_table_android_builds
 */
class m191127_132948_create_table_android_builds extends Migration
{
    private $tableName = 'admin.android_builds';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'filename' => $this->string()->notNull(),
            'version' => $this->string()->notNull(),
            'update_required' => $this->boolean()->defaultValue(false),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer()
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
