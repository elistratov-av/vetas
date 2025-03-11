<?php

use app\commands\migrate\Migration;

/**
 * Class m190627_120929_2049_shelter_create_shelter_guests
 */
class m190627_120929_2049_shelter_create_shelter_guests extends Migration
{
    private $tableName = 'shelter_guests';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%' . $this->tableName . '}}', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->notNull()->comment('ID приюта'),
            'id_pet' => $this->integer()->notNull(),
            'arrival_date' => $this->date()->notNull()->comment('Дата поступления'),
            'arrival_comment' => $this->text()->comment('Комментарий при поступлении'),
            'departure_date' => $this->date()->comment('Дата выбытия'),
            'departure_comment' => $this->text()->comment('Комментарий при выбытии'),
            'departure_reason' => $this->string()->comment('Причина выбытия'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);

        $this->createIndex(
            'idx_' . $this->tableName . '_id_pet',
            '{{%' . $this->tableName . '}}',
            'id_pet'
        );

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_pet',
            '{{%' . $this->tableName . '}}',
            'id_pet',
            'pets',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx_' . $this->tableName . '_id_organization',
            '{{%' . $this->tableName . '}}',
            'id_organization'
        );

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_organization',
            '{{%' . $this->tableName . '}}',
            'id_organization',
            'organizations',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx_' . $this->tableName . '_departure_reason',
            '{{%' . $this->tableName . '}}',
            'departure_reason'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%' . $this->tableName . '}}');
    }
}
