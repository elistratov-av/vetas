<?php

use app\commands\migrate\Migration;

/**
 * Class m230303_140800_brigades_table_create
 */
class m230303_140800_brigades_table_create extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.brigades', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'car_model' => $this->string(255),
            'car_number' => $this->string(255),
            'pass_number' => $this->string(255),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);

        $this->createTable('public.brigades_timesheets', [
            'id' => $this->primaryKey(),
            // 'date' => $this->tsrange()->notNull()->comment('Дата'),
            'id_brigade' => $this->integer()->comment('Ссылка на бригаду'),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime()
        ]);
        $this->execute("ALTER TABLE public.brigades_timesheets ADD COLUMN date tsrange; ");
        
        // creates index for column `id_brigade`
        $this->createIndex(
            'idx-brigades_timesheets-id_brigade',
            'public.brigades_timesheets',
            'id_brigade'
        );

        $this->createTable('public.brigades_specialists', [
            'id_brigade' => $this->integer()->comment('Ссылка на бригаду'),
            'id_specialist' => $this->integer()->comment('Ссылка на специалиста'),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime()
        ]);

        $this->addPrimaryKey('brigades_specialists_pkey', 'public.brigades_specialists', ['id_brigade', 'id_specialist']);
        $this->addCommentOnTable('brigades_specialists', 'Таблица связи бригад и специалистов');

        $this->addForeignKey(
            'fk-brigades_specialists-id_brigade',
            'brigades_specialists',
            'id_brigade',
            'public.brigades',
            'id'
        );

        $this->addForeignKey(
            'fk-brigades_specialists-id_specialist',
            'brigades_specialists',
            'id_specialist',
            'public.specialists',
            'id'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('brigades');

        $this->dropTable('brigades_timesheets');

        $this->dropForeignKey('fk-brigades_specialists-id_visit', 'brigades_specialists');
        $this->dropForeignKey('fk-brigades_specialists-id_specialist', 'brigades_specialists');
        $this->dropTable('brigades_specialists');

        return true;
    }
}
