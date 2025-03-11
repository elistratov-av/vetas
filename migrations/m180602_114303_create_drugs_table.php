<?php

use yii\db\Migration;

/**
 * Handles the creation of table `drugs`.
 */
class m180602_114303_create_drugs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('drugs', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->unique(),
            'id_tmc_type' => $this->integer(),
            'id_manufactured' => $this->integer(),
            'id_representation' => $this->string(255),
            'form' => $this->string(255)->notNull(),
            'form_description' => $this->string(255),
            'unit' => $this->integer(),
            'id_measure' => $this->integer(),
            'id_active_substance' => $this->integer(),
            'active_substance_unit' => $this->integer(),
            'id_active_substance_measure' => $this->integer(),
            'excipients' => $this->string(255),
            'packaging' => $this->string(255),
            'id_file_packaging_image' => $this->integer(),
            'basis' => $this->string(255),
        ]);

        $this->createIndex('drug-idx', 'drugs', 'id', true);

        $this->addForeignKey('fk-drugs-id_tmc_type',  'drugs', 'id_tmc_type', 'tmc_types', 'id', 'NO ACTION' );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('drugs');
    }
}
