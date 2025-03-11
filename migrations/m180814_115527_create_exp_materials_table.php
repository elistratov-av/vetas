<?php

use yii\db\Migration;

/**
 * Handles the creation of table `exp_materials`.
 * Has foreign keys to the tables:
 *
 * - `tmc_types`
 * - `measures`
 */
class m180814_115527_create_exp_materials_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE TABLE public.exp_materials
(
  id_measure integer NOT NULL, -- Ссылка на справочник единиц измерений
  description text,
  CONSTRAINT exp_materials_pkey PRIMARY KEY (id)
)
INHERITS (public.tmc)
WITH (
  OIDS=FALSE
);
SQL;
        $this->execute($sql);
        
        // creates index for column `id_tmc_type`
        $this->createIndex(
            'idx-exp_materials-id_tmc_type',
            'exp_materials',
            'id_tmc_type'
        );

        // add foreign key for table `tmc_types`
        $this->addForeignKey(
            'fk-exp_materials-id_tmc_type',
            'exp_materials',
            'id_tmc_type',
            'tmc_types',
            'id',
            'CASCADE'
        );

        // creates index for column `id_measure`
        $this->createIndex(
            'idx-exp_materials-id_measure',
            'exp_materials',
            'id_measure'
        );

        // add foreign key for table `measures`
        $this->addForeignKey(
            'fk-exp_materials-id_measure',
            'exp_materials',
            'id_measure',
            'measures',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `tmc_types`
        $this->dropForeignKey(
            'fk-exp_materials-id_tmc_type',
            'exp_materials'
        );

        // drops index for column `id_tmc_type`
        $this->dropIndex(
            'idx-exp_materials-id_tmc_type',
            'exp_materials'
        );

        // drops foreign key for table `measures`
        $this->dropForeignKey(
            'fk-exp_materials-id_measure',
            'exp_materials'
        );

        // drops index for column `id_measure`
        $this->dropIndex(
            'idx-exp_materials-id_measure',
            'exp_materials'
        );

        $this->dropTable('exp_materials');
    }
}
