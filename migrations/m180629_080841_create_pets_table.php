<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pets`.
 * Has foreign keys to the tables:
 *
 * - `species`
 * - `breeds`
 * - `organizations`
 * - `identification_types`
 * - `reg_expire_reasons`
 * - `files`
 */
class m180629_080841_create_pets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pets', [
            'id' => $this->primaryKey(),
            'identification_code' => $this->string(50),
            'reg_expire_date' => $this->date(),
            'birthday' => $this->date(),
            'name' => $this->string(50),
            'sex' => $this->string(1),
            'id_owner' => $this->integer(),
            
            'id_species' => $this->integer(),
            'id_breed' => $this->integer(),
            'id_reg_organization' => $this->integer(),
            'id_ident_type' => $this->integer(),
            'id_reg_expire_reason' => $this->integer(),
            'photo' => $this->integer(),
        ]);

        // creates index for column `id_species`
        $this->createIndex(
            'idx-pets-id_species',
            'pets',
            'id_species'
        );

        // add foreign key for table `species`
        $this->addForeignKey(
            'fk-pets-id_species',
            'pets',
            'id_species',
            'species',
            'id',
            'CASCADE'
        );

        // creates index for column `id_breed`
        $this->createIndex(
            'idx-pets-id_breed',
            'pets',
            'id_breed'
        );

        // add foreign key for table `breeds`
        $this->addForeignKey(
            'fk-pets-id_breed',
            'pets',
            'id_breed',
            'breeds',
            'id',
            'CASCADE'
        );

        // creates index for column `id_reg_organization`
        $this->createIndex(
            'idx-pets-id_reg_organization',
            'pets',
            'id_reg_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-pets-id_reg_organization',
            'pets',
            'id_reg_organization',
            'organizations',
            'id',
            'CASCADE'
        );

        // creates index for column `id_ident_type`
        $this->createIndex(
            'idx-pets-id_ident_type',
            'pets',
            'id_ident_type'
        );

        // add foreign key for table `identification_types`
        $this->addForeignKey(
            'fk-pets-id_ident_type',
            'pets',
            'id_ident_type',
            'identification_types',
            'id',
            'CASCADE'
        );

        // creates index for column `id_reg_expire_reason`
        $this->createIndex(
            'idx-pets-id_reg_expire_reason',
            'pets',
            'id_reg_expire_reason'
        );

        // add foreign key for table `reg_expire_reasons`
        $this->addForeignKey(
            'fk-pets-id_reg_expire_reason',
            'pets',
            'id_reg_expire_reason',
            'reg_expire_reasons',
            'id',
            'CASCADE'
        );

        // creates index for column `photo`
        $this->createIndex(
            'idx-pets-photo',
            'pets',
            'photo'
        );

        // add foreign key for table `files`
        $this->addForeignKey(
            'fk-pets-photo',
            'pets',
            'photo',
            'files',
            'id',
            'CASCADE'
        );
        
        $this->createIndex('idx_identification_code', 'pets', 'identification_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `species`
        $this->dropForeignKey(
            'fk-pets-id_species',
            'pets'
        );

        // drops index for column `id_species`
        $this->dropIndex(
            'idx-pets-id_species',
            'pets'
        );

        // drops foreign key for table `breeds`
        $this->dropForeignKey(
            'fk-pets-id_breed',
            'pets'
        );

        // drops index for column `id_breed`
        $this->dropIndex(
            'idx-pets-id_breed',
            'pets'
        );

        // drops foreign key for table `organizations`
        $this->dropForeignKey(
            'fk-pets-id_reg_organization',
            'pets'
        );

        // drops index for column `id_reg_organization`
        $this->dropIndex(
            'idx-pets-id_reg_organization',
            'pets'
        );

        // drops foreign key for table `identification_types`
        $this->dropForeignKey(
            'fk-pets-id_ident_type',
            'pets'
        );

        // drops index for column `id_ident_type`
        $this->dropIndex(
            'idx-pets-id_ident_type',
            'pets'
        );

        // drops foreign key for table `reg_expire_reasons`
        $this->dropForeignKey(
            'fk-pets-id_reg_expire_reason',
            'pets'
        );

        // drops index for column `id_reg_expire_reason`
        $this->dropIndex(
            'idx-pets-id_reg_expire_reason',
            'pets'
        );

        // drops foreign key for table `files`
        $this->dropForeignKey(
            'fk-pets-photo',
            'pets'
        );

        // drops index for column `photo`
        $this->dropIndex(
            'idx-pets-photo',
            'pets'
        );

        $this->dropTable('pets');
    }
}
