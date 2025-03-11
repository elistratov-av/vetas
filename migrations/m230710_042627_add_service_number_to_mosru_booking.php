<?php

use app\commands\migrate\Migration;

/**
 * Class m230710_042627_add_service_number_to_mosru_booking
 */
class m230710_042627_add_service_number_to_mosru_booking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-booking-id_owner', 'mosru.booking');
        $this->dropColumn('mosru.booking', 'id_owner');
        $this->addColumn('mosru.booking', 'service_number', $this->string()->notNull());
        $this->addColumn('mosru.booking', 'updated_at', $this->dateTime()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('mosru.booking', 'service_number');
        $this->dropColumn('mosru.booking', 'updated_at');
        $this->addColumn('mosru.booking', 'id_owner', $this->integer()->notNull());

        $this->addForeignKey(
            'fk-booking-id_owner',
            'mosru.booking',
            'id_owner',
            'public.pet_owners',
            'id'
        );
    }
}
