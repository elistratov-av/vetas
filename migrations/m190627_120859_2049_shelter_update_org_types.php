<?php

use app\commands\migrate\Migration;

/**
 * Class m190627_120859_2049_shelter_update_org_types
 */
class m190627_120859_2049_shelter_update_org_types extends Migration
{
    private $tableName = 'org_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'is_tech', $this->boolean()->defaultValue(false)->comment('Такой тип организации недоступен для создания с фронта'));

        $this->createIndex(
            'idx_' . $this->tableName . '_' . 'is_tech',
            '{{%' . $this->tableName . '}}',
            'is_tech'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_' . $this->tableName . '_' . 'is_tech', '{{%' . $this->tableName . '}}');

        $this->dropColumn('{{%' . $this->tableName . '}}', 'is_tech');
    }
}
