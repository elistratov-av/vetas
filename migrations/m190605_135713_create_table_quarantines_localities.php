<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_135713_create_table_quarantines_localities
 */
class m190605_135713_create_table_quarantines_localities extends Migration
{
    private $tableName = 'quarantines_localities';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%' . $this->tableName . '}}', [
            'id' => $this->primaryKey(),
            'id_quarantine' => $this->integer()->notNull(),
            'territory' => $this->json(),
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%' . $this->tableName . '}}');
    }
}
