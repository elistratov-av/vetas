<?php

use app\commands\migrate\Migration;

/**
 * Class m190409_155614_update_visits_1732_add_fields
 */
class m190409_155614_update_visits_1732_add_fields extends Migration
{
    private $tableName = 'visits';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'type', $this->string(32));
        $this->addColumn('{{%' . $this->tableName . '}}', 'visit_to_address', $this->text());
        $this->addColumn('{{%' . $this->tableName . '}}', 'description', $this->text());

        $this->createIndex('visits_type_idx', '{{%' . $this->tableName . '}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('visits_type_idx', '{{%' . $this->tableName . '}}');

        foreach (['type', 'visit_to_address', 'description'] as $column) {
            $this->dropColumn('{{%' . $this->tableName . '}}', $column);
        }
    }
}
