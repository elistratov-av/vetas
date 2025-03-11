<?php

use yii\db\Migration;

/**
 * Handles the creation of table `vaccines`.
 * Has foreign keys to the tables:
 *
 * - `tmc_types`
 * - `drug_orgs`
 * - `drug_orgs`
 * - `drug_orgs`
 * - `measures`
 */
class m180803_145409_add_indexes_and_keys_to_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("update vaccines set id_dealer = null where (select id from drug_orgs where id = id_dealer) is null");
        $this->execute("update vaccines set id_produced = null where (select id from drug_orgs where id = id_produced) is null");
        $this->execute("update vaccines set id_registered = null where (select id from drug_orgs where id = id_registered) is null");
        $this->execute("update vaccines set id_measure = null where (select id from measures where id = id_measure) is null");

        // creates index for column `id_tmc_type`
        /*$this->createIndex(
            'idx-vaccines-id_tmc_type',
            'vaccines',
            'id_tmc_type'
        );

        // add foreign key for table `tmc_types`
        $this->addForeignKey(
            'fk-vaccines-id_tmc_type',
            'vaccines',
            'id_tmc_type',
            'tmc_types',
            'id',
            'CASCADE'
        );

        // creates index for column `id_registered`
        $this->createIndex(
            'idx-vaccines-id_registered',
            'vaccines',
            'id_registered'
        );

        // add foreign key for table `drug_orgs`
        $this->addForeignKey(
            'fk-vaccines-id_registered',
            'vaccines',
            'id_registered',
            'drug_orgs',
            'id',
            'CASCADE'
        );

        // creates index for column `id_produced`
        $this->createIndex(
            'idx-vaccines-id_produced',
            'vaccines',
            'id_produced'
        );

        // add foreign key for table `drug_orgs`
        $this->addForeignKey(
            'fk-vaccines-id_produced',
            'vaccines',
            'id_produced',
            'drug_orgs',
            'id',
            'CASCADE'
        );

        // creates index for column `id_dealer`
        $this->createIndex(
            'idx-vaccines-id_dealer',
            'vaccines',
            'id_dealer'
        );

        // add foreign key for table `drug_orgs`
        $this->addForeignKey(
            'fk-vaccines-id_dealer',
            'vaccines',
            'id_dealer',
            'drug_orgs',
            'id',
            'CASCADE'
        );

        // creates index for column `id_measure`
        $this->createIndex(
            'idx-vaccines-id_measure',
            'vaccines',
            'id_measure'
        );

        // add foreign key for table `measures`
        $this->addForeignKey(
            'fk-vaccines-id_measure',
            'vaccines',
            'id_measure',
            'measures',
            'id',
            'CASCADE'
        );*/
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `tmc_types`
        $this->dropForeignKey(
            'fk-vaccines-id_tmc_type',
            'vaccines'
        );

        // drops index for column `id_tmc_type`
        $this->dropIndex(
            'idx-vaccines-id_tmc_type',
            'vaccines'
        );

        // drops foreign key for table `drug_orgs`
        $this->dropForeignKey(
            'fk-vaccines-id_registered',
            'vaccines'
        );

        // drops index for column `id_registered`
        $this->dropIndex(
            'idx-vaccines-id_registered',
            'vaccines'
        );

        // drops foreign key for table `drug_orgs`
        $this->dropForeignKey(
            'fk-vaccines-id_produced',
            'vaccines'
        );

        // drops index for column `id_produced`
        $this->dropIndex(
            'idx-vaccines-id_produced',
            'vaccines'
        );

        // drops foreign key for table `drug_orgs`
        $this->dropForeignKey(
            'fk-vaccines-id_dealer',
            'vaccines'
        );

        // drops index for column `id_dealer`
        $this->dropIndex(
            'idx-vaccines-id_dealer',
            'vaccines'
        );

        // drops foreign key for table `measures`
        $this->dropForeignKey(
            'fk-vaccines-id_measure',
            'vaccines'
        );

        // drops index for column `id_measure`
        $this->dropIndex(
            'idx-vaccines-id_measure',
            'vaccines'
        );
    }
}
