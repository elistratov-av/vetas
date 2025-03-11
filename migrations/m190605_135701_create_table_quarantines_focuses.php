<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_135701_create_table_quarantines_focuses
 */
class m190605_135701_create_table_quarantines_focuses extends Migration
{
    private $tableName = 'quarantines_focuses';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%' . $this->tableName . '}}', [
            'id' => $this->primaryKey(),
            'id_quarantine' => $this->integer()->notNull(),
            'address' => $this->string()->notNull(),
            'identification_date' => $this->date()->notNull(),
            'id_pet' => $this->integer(),
            'coords' => 'geometry',
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);

        $this->createIndex(
            'idx_' . $this->tableName . '_id_quarantine',
            '{{%' . $this->tableName . '}}',
            'id_quarantine'
        );

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_quarantine',
            '{{%' . $this->tableName . '}}',
            'id_quarantine',
            'quarantines',
            'id',
            'CASCADE'
        );

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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%' . $this->tableName . '}}');
    }
}
