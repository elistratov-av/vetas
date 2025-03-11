<?php

use app\commands\migrate\Migration;

/**
 * Class m190627_120749_2049_shelter_update_pets
 */
class m190627_120749_2049_shelter_update_pets extends Migration
{
    private $tableName = 'pets';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'color', $this->text()->null()->comment('Окрас'));
        $this->addColumn('{{%' . $this->tableName . '}}', 'characteristics', $this->text()->null()->comment('Особые приметы'));
        $this->addColumn('{{%' . $this->tableName . '}}', 'id_created_organization', $this->integer()->null()->comment('ID организации, в которой было создано животное'));

        $this->createIndex(
            'idx_' . $this->tableName . '_' . 'id_created_organization',
            '{{%' . $this->tableName . '}}',
            'id_created_organization'
        );
        $this->addForeignKey(
            'fk_' . $this->tableName . '_' . 'id_created_organization',
            '{{%' . $this->tableName . '}}',
            'id_created_organization',
            'organizations',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_' . $this->tableName . '_' . 'id_created_organization', '{{%' . $this->tableName . '}}');
        $this->dropIndex('idx_' . $this->tableName . '_' . 'id_created_organization', '{{%' . $this->tableName . '}}');

        foreach (['color', 'characteristics', 'id_created_organization'] as $column) {
            $this->dropColumn('{{%' . $this->tableName . '}}', $column);
        }
    }
}
