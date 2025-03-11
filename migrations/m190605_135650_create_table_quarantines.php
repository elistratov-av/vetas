<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_135650_create_table_quarantines
 */
class m190605_135650_create_table_quarantines extends Migration
{
    private $tableName = 'quarantines';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%' . $this->tableName . '}}', [
            'id' => $this->primaryKey(),
            'id_disease' => $this->integer()->notNull(),
            'threatened_area' => $this->text()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date(),
            'fact_end_date' => $this->date(),
            'comments' => $this->text(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);

        $this->createIndex(
            'idx_' . $this->tableName . '_id_disease',
            '{{%' . $this->tableName . '}}',
            'id_disease'
        );

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_disease',
            '{{%' . $this->tableName . '}}',
            'id_disease',
            'diseases',
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
