<?php

use app\commands\migrate\Migration;

/**
 * Class m210708_111840_add_reason_to_quarantine_absent_pet_table
 */
class m210708_111840_add_reason_to_quarantine_absent_pet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('visits', 'absent_pet_reason');
        $this->addColumn('quarantine_absent_pets', 'absent_pet_reason', $this->smallInteger()->notNull()->defaultValue(40)->comment('Причина отсутствия животного при поквартирном обходе'));
        $this->addColumn('quarantine_absent_pets', 'id_pet', $this->integer()->comment('Идентификатор отсутствующего животного'));

        $this->addForeignKey(
            'fk-quarantine_absent_pets-id_pet',
            'quarantine_absent_pets',
            'id_pet',
            'pets',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-quarantine_absent_pets-id_pet', 'quarantine_absent_pets');
        $this->dropColumn('quarantine_absent_pets', 'absent_pet_reason');
        $this->dropColumn('quarantine_absent_pets', 'id_pet');
        $this->addColumn('visits', 'absent_pet_reason', $this->smallInteger()->comment('Причина отсутствия животного при поквартирном обходе'));
    }
}
