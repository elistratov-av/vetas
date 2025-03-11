<?php

use app\commands\migrate\Migration;

/**
 * Class m210624_142954_add_visits_absent_animal_reason
 */
class m210624_142954_add_visits_absent_animal_reason extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'absent_pet_reason', $this->smallInteger()->comment('Причина отсутствия животного при поквартирном обходе'));

        $this->createTable('quarantine_absent_pets', [
            'id' => $this->primaryKey(),
            'id_quarantine' => $this->integer()->notNull()->comment('Карантин в рамках которого зафиксировано отсутствие животного'),
            'full_address' => $this->string()->notNull()->comment('Адрес по которому отмечено отсутствие животного'),
            'date' => $this->date()->notNull()->comment('Дата обхода'),
        ]);

        $this->addForeignKey('fk-quarantine_absent_pets_quarantines-id_quarantine',
            'quarantine_absent_pets',
            'id_quarantine',
            'quarantines',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'absent_pet_reason');
        $this->dropTable('quarantine_absent_pets');
    }
}
