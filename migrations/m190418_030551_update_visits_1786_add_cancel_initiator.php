<?php

use app\commands\migrate\Migration;

/**
 * Class m190418_030551_update_visits_1786_add_cancel_initiator
 */
class m190418_030551_update_visits_1786_add_cancel_initiator extends Migration
{
    private $tableName = 'visits';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'cancel_initiator', $this->string(32));

        $this->createIndex('visits_cancel_initiator_idx', '{{%' . $this->tableName . '}}', 'cancel_initiator');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('visits_cancel_initiator_idx', '{{%' . $this->tableName . '}}');

        $this->dropColumn('{{%' . $this->tableName . '}}', 'cancel_initiator');
    }
}
