<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visits`.
 * Has foreign keys to the tables:
 *
 * - `pet_owners`
 * - `pets`
 * - `organizations`
 */
class m180706_115354_create_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visits', [
            'id' => $this->primaryKey(),
            'status' => $this->string()->comment('Статус'),
            'is_paid' => $this->boolean()->comment('Оплачен/не оплачен'),
            'start_dttm' => $this->integer()->comment('Начало приема '),
            'duration' => $this->integer()->comment('Продолжительность приема в минутах'),
            'change_reason' => $this->text()->comment('Причина'),
            'id_owner' => $this->integer()->comment('Ссылка на владельца животного'),
            'id_pet' => $this->integer()->comment('Ссылка на животное'),
            'id_organization' => $this->integer()->comment('Ссылка на организация'),
        ]);
        $this->addCommentOnTable('visits', 'Записи на прием');

        // creates index for column `id_owner`
        $this->createIndex(
            'idx-visits-id_owner',
            'visits',
            'id_owner'
        );

        // add foreign key for table `pet_owners`
        $this->addForeignKey(
            'fk-visits-id_owner',
            'visits',
            'id_owner',
            'pet_owners',
            'id',
            'CASCADE'
        );

        // creates index for column `id_pet`
        $this->createIndex(
            'idx-visits-id_pet',
            'visits',
            'id_pet'
        );

        // add foreign key for table `pets`
        $this->addForeignKey(
            'fk-visits-id_pet',
            'visits',
            'id_pet',
            'pets',
            'id',
            'CASCADE'
        );

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-visits-id_organization',
            'visits',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-visits-id_organization',
            'visits',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `pet_owners`
        $this->dropForeignKey(
            'fk-visits-id_owner',
            'visits'
        );

        // drops index for column `id_owner`
        $this->dropIndex(
            'idx-visits-id_owner',
            'visits'
        );

        // drops foreign key for table `pets`
        $this->dropForeignKey(
            'fk-visits-id_pet',
            'visits'
        );

        // drops index for column `id_pet`
        $this->dropIndex(
            'idx-visits-id_pet',
            'visits'
        );

        // drops foreign key for table `organizations`
        $this->dropForeignKey(
            'fk-visits-id_organization',
            'visits'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-visits-id_organization',
            'visits'
        );

        $this->dropTable('visits');
    }
}
