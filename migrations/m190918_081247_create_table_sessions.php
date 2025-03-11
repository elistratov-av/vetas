<?php

use app\commands\migrate\Migration;

/**
 * Class m190918_081247_create_table_sessions
 */
class m190918_081247_create_table_sessions extends Migration
{
    private $tableName = 'admin.sessions';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'id_user' => $this->integer(),
            'token_hash' => $this->string(),
            'valid_until' => $this->dateTime(0),
            'last_active_at' => $this->dateTime(0),
            'ip' => $this->string(),
            'ua' => $this->string(1000),
            'ua_hash' => $this->string(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);

        $tn = str_replace('.', '_', $this->tableName);

        $this->createIndex(
            'idx_' . $tn . '_id_user',
            $this->tableName,
            'id_user'
        );

        $this->addForeignKey(
            'fk_' . $tn . '_id_user',
            $this->tableName,
            'id_user',
            'public.users',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
