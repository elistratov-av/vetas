<?php

use app\commands\migrate\Migration;
use app\models\db\ShelterGuests;
use app\models\db\Aviary;

/**
 * Class m211215_062657_add_fields_to_shelter_guests
 */
class m211215_062657_add_fields_to_shelter_guests extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(ShelterGuests::tableName(), 'status', $this->string(255)->comment('Статус'));
        $this->addColumn(ShelterGuests::tableName(), 'is_quarantine', $this->boolean()->notNull()->defaultValue(false)->comment('Находится ли животное на карантине'));
        $this->addColumn(ShelterGuests::tableName(), 'quarantine_from', $this->date()->comment('Дата начала карантина'));
        $this->addColumn(ShelterGuests::tableName(), 'quarantine_to', $this->date()->comment('Дата окончания карантина'));
        $this->addColumn(ShelterGuests::tableName(), 'arrival_reason', $this->string()->comment('Основание прибытия'));
        $this->addColumn(ShelterGuests::tableName(), 'arrival_act_number', $this->string(255)->comment('Номер акта приема'));
        $this->addColumn(ShelterGuests::tableName(), 'arrival_act_number_date', $this->date()->comment('Дата акта приема'));
        $this->addColumn(ShelterGuests::tableName(), 'arrival_work_order', $this->string(255)->comment('Номер заказ-наряда'));
        $this->addColumn(ShelterGuests::tableName(), 'arrival_work_order_date', $this->date()->comment('Дата заказ-наряда'));
        $this->addColumn(ShelterGuests::tableName(), 'catching_act_number', $this->string(255)->comment('Номер акта отлова'));
        $this->addColumn(ShelterGuests::tableName(), 'catching_act_date', $this->date()->comment('Дата акта отлова'));
        $this->addColumn(ShelterGuests::tableName(), 'catching_address', $this->integer()->comment('Адрес места отлова'));
        $this->addColumn(ShelterGuests::tableName(), 'is_catching_video', $this->boolean()->notNull()->defaultValue(false)->comment('Есть ли видео с места отлова'));
        $this->addColumn(ShelterGuests::tableName(), 'catching_video', $this->string()->comment('Видео с места отлова'));
        $this->addColumn(ShelterGuests::tableName(), 'departure_specialist', $this->integer()->comment('Специалист выбывший'));
        $this->addColumn(ShelterGuests::tableName(), 'aviary_id', $this->integer()->comment('Вольер'));
        $this->addColumn(ShelterGuests::tableName(), 'socialized', $this->boolean()->defaultValue(false)->comment('Социализация'));
        

        $this->addForeignKey(
            'fk-shelter_guests-aviary_id',
            $tableName,
            'aviary_id',
            Aviary::tableName(),
            'id',
            'SET NULL'
        );        

        $this->addForeignKey(
            'fk-shelter_guests-fias_addresses_id',
            ShelterGuests::tableName(),
            'catching_address',
            'fias_addresses',
            'id'
        );
        
        $this->addColumn('pets', 'character', $this->string()->comment('Характер'));

        $this->execute("INSERT INTO 
        identification_types(name)
        VALUES ('метка');
        ");

        $this->execute("INSERT INTO 
        pet_ref_size(title, created_by, updated_by)
        VALUES ('Мелкий', 2696, 2696), ('Средний', 2696, 2696), ('Крупный', 2696, 2696);
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-shelter_guests-fias_addresses_id', 'shelter_guests');
        
        $this->dropColumn(ShelterGuests::tableName(), 'status');
        $this->dropColumn(ShelterGuests::tableName(), 'is_quarantine');
        $this->dropColumn(ShelterGuests::tableName(), 'quarantine_from');
        $this->dropColumn(ShelterGuests::tableName(), 'quarantine_to');
        $this->dropColumn(ShelterGuests::tableName(), 'arrival_reason');
        $this->dropColumn(ShelterGuests::tableName(), 'arrival_act_number');
        $this->dropColumn(ShelterGuests::tableName(), 'arrival_act_number_date');
        $this->dropColumn(ShelterGuests::tableName(), 'arrival_work_order');
        $this->dropColumn(ShelterGuests::tableName(), 'arrival_work_order_date');
        $this->dropColumn(ShelterGuests::tableName(), 'catching_act_number');
        $this->dropColumn(ShelterGuests::tableName(), 'catching_act_date');
        $this->dropColumn(ShelterGuests::tableName(), 'catching_address');
        $this->dropColumn(ShelterGuests::tableName(), 'is_catching_video');
        $this->dropColumn(ShelterGuests::tableName(), 'catching_video');
        $this->dropColumn(ShelterGuests::tableName(), 'departure_specialist');
        $this->dropColumn(ShelterGuests::tableName(), 'aviary_id');
        $this->dropColumn(ShelterGuests::tableName(), 'socialized');
        
        $this->dropColumn('pets', 'character');

        $this->dropForeignKey('fk-shelter_guests-aviary_id', $tableName);
        $this->dropColumn($tableName, 'aviary_id');

        
        $this->execute("DELETE FROM identification_types WHERE name = 'метка';");
        $this->execute("DELETE FROM pet_ref_size WHERE title IN ('Маленький', 'Средний');");

        return false;
    }

}