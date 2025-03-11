<?php

use app\commands\migrate\Migration;

/**
 * Class m191022_085306_2418_create_table_password_history
 */
class m181022_085306_2418_create_table_password_history extends Migration
{
    private $tableName = 'public.password_history';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'id_user' => $this->integer(),
            'target' => $this->tinyInteger(1)->notNull(),
            'password' => $this->string(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);

        $tn = str_replace('.', '_', $this->tableName);

        $this->createIndex(
            'idx_' . $tn . '_id_user_target',
            $this->tableName,
            ['id_user', 'target']
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
