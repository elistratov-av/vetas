<?php

use app\commands\migrate\Migration;

/**
 * Class m240206_085519_rename_pet_hotel_requst_status
 */
class m240206_085519_rename_pet_hotel_requst_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('public.pet_hotel_request_status', ['code' => 'Подтверждённая бронь'], ['id' => 1]);
        $this->update('public.pet_hotel_request_status', ['name' => 'Статус "Подтверждённая бронь"'], ['id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240206_085519_rename_pet_hotel_requst_status cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240206_085519_rename_pet_hotel_requst_status cannot be reverted.\n";

        return false;
    }
    */
}
