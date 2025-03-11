<?php

use app\commands\migrate\Migration;

/**
 * Class m231219_092752_add_jotting_fields_for_hotel_req
 */
class m231219_092752_add_jotting_fields_for_hotel_req extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_hotel_request', 'animal_weight', $this->float()->after('date_to'));
        $this->addColumn('pet_hotel_request', 'count_feeding', $this->integer()->after('animal_weight'));
        $this->addColumn('pet_hotel_request', 'animal_diet_dry', $this->float()->after('count_feeding'));
        $this->addColumn('pet_hotel_request', 'animal_diet_wet', $this->float()->after('animal_diet_dry'));
        $this->addColumn('pet_hotel_request', 'feeding_rate', $this->float()->after('animal_diet_wet'));
        $this->addColumn('pet_hotel_request', 'count_walking', $this->integer()->after('feeding_rate'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_hotel_request', 'animal_weight');
        $this->dropColumn('pet_hotel_request', 'count_feeding');
        $this->dropColumn('pet_hotel_request', 'animal_diet_dry');
        $this->dropColumn('pet_hotel_request', 'animal_diet_wet');
        $this->dropColumn('pet_hotel_request', 'feeding_rate');
        $this->dropColumn('pet_hotel_request', 'count_walking');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231219_092752_add_jotting_fields_for_hotel_req cannot be reverted.\n";

        return false;
    }
    */
}
