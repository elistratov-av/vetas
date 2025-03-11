<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_075717_add_timestamp_to_anminalid
 */
class m190811_075717_add_timestamp_to_anminalid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('animalid.id_mapping', 'timestamp', "timestamp without time zone NOT NULL default now()::timestamp without time zone");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('animalid.id_mapping', 'timestamp');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190811_075717_add_timestamp_to_anminalid cannot be reverted.\n";

        return false;
    }
    */
}
