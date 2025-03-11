<?php

use app\commands\migrate\Migration;

/**
 * Class m190820_053441_add_timestamp_to_animalid_errors_table
 */
class m190826_114025_add_timestamp_to_animalid_errors_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('animalid.errors', 'timestamp',
            "timestamp without time zone NOT NULL default now()::timestamp without time zone");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('animalid.errors', 'timestamp');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190820_053441_add_timestamp_to_animalid_errors_table cannot be reverted.\n";

        return false;
    }
    */
}
