<?php

use app\commands\migrate\Migration;

/**
 * Class m240617_092813_add_death_reason_id_to_shelter_guests
 */
class m240617_092813_add_death_reason_id_to_shelter_guests extends Migration
{
    public function safeUp()
    {
        $this->addColumn('public.shelter_guests', 'death_reason_id', $this->integer()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('public.shelter_guests', 'death_reason_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240617_092813_add_death_reason_id_to_shelter_guests cannot be reverted.\n";

        return false;
    }
    */
}
