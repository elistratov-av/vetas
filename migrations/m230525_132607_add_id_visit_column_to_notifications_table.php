<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%notifications}}`.
 */
class m230525_132607_add_id_visit_column_to_notifications_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notifications', 'id_visit', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('notifications', 'id_visit');
    }
}
