<?php

use app\commands\migrate\Migration;
use app\models\db\Aviary;
use app\models\db\ShelterGuests;

class m211217_148134_add_aviary_ref_to_shelter_guests_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = ShelterGuests::tableName();

        $this->addColumn($tableName, 'aviary_id', $this->integer());
        $this->addForeignKey(
            'fk-shelter_guests-aviary_id',
            $tableName,
            'aviary_id',
            Aviary::tableName(),
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableName = ShelterGuests::tableName();
        $this->dropForeignKey('fk-shelter_guests-aviary_id', $tableName);
        $this->dropColumn($tableName, 'aviary_id');
    }
}
