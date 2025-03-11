<?php

use app\commands\migrate\Migration;

/**
 * Class m230601_113900_duplicates_table_create
 */
class m230601_113900_duplicates_table_create extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.duplicates', [
            'id' => $this->primaryKey(),
            'id_main' => $this->integer()->comment('ID главной записи'),
            'type' => $this->integer(1)->comment('1 - вледельцы, 2 - животные'),
            'temp' => $this->boolean()->defaultValue(false)->comment('Временная запись'),
            'created_at' => $this->dateTime()
        ]);

        $this->execute("ALTER TABLE public.duplicates ADD COLUMN ids integer[]; ");
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('duplicates');

        return true;
    }
}