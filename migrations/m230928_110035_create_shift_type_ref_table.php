<?php

use app\commands\migrate\Migration;

/**
 * Class m230928_110035_create_shift_type_ref_table
 */
class m230928_110035_create_shift_type_ref_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            'shift_type_ref',
            [
                'id' => $this->primaryKey(),
                'id_organization' => $this->integer()->notNull()->comment('Организация'),
                'title' => $this->string()->notNull()->comment('Наименовании смены'),
                'beginning_of_shift' => $this->dateTime()->notNull()->comment('Начало смены'),
                'end_of_shift' => $this->dateTime()->notNull()->comment('Окончание смены'),
                'shift_type' => $this->integer()->notNull()->comment('Тип смены'),
            ]
        );

        $this->addForeignKey(
            'idx-shift_type_ref-id_organization',
            'shift_type_ref',
            'id_organization',
            'organizations',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'idx-shift_type_ref-shift_type',
            'shift_type_ref',
            'shift_type',
            'shift_type',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-shift_type_ref-title', 'shift_type_ref', 'title');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('shift_type_ref');
    }
}
