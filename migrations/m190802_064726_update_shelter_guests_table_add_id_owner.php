<?php

use app\commands\migrate\Migration;

/**
 * Class m190802_064726_update_shelter_guests_table_add_id_owner
 */
class m190802_064726_update_shelter_guests_table_add_id_owner extends Migration
{
    private $tableName = 'shelter_guests';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'id_owner', $this->integer());

        $this->createIndex(
            'idx_' . $this->tableName . '_id_owner',
            '{{%' . $this->tableName . '}}',
            'id_owner'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%' . $this->tableName . '}}', 'id_owner');
    }
}
