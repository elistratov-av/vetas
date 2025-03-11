<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_135800_create_table_quarantines_localities_to_focuses
 */
class m190605_135800_create_table_quarantines_localities_to_focuses extends Migration
{
    private $tableName = 'quarantines_localities_to_focuses';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%' . $this->tableName . '}}', [
            'id' => $this->primaryKey(),
            'id_locality' => $this->integer()->notNull(),
            'id_focus' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx_' . $this->tableName . '_id_locality',
            '{{%' . $this->tableName . '}}',
            'id_locality'
        );

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_locality',
            '{{%' . $this->tableName . '}}',
            'id_locality',
            'quarantines_localities',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx_' . $this->tableName . '_id_focus',
            '{{%' . $this->tableName . '}}',
            'id_focus'
        );

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_focus',
            '{{%' . $this->tableName . '}}',
            'id_focus',
            'quarantines_focuses',
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
