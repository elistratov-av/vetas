<?php

use yii\db\Migration;

/**
 * Handles the creation of table `equipments`.
 */
class m180604_060318_create_equipments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('equipments', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->unique(),
            'id_tmc_type' => $this->integer(),
            'description' => $this->string(),
        ]);

        $this->createIndex('name-idx', 'equipments', 'name', true);

        $this->addForeignKey('fk-equipments-id_tmc_type',  'equipments', 'id_tmc_type', 'tmc_types', 'id', 'NO ACTION' );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('equipments');
    }
}
