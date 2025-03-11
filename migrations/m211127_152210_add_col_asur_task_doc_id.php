<?php

use yii\db\Migration;

/**
 * Class m211127_152210_add_col_asur_task_doc_id
 */
class m211127_152210_add_col_asur_task_doc_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('asur.task', 'doc_id', 'string');
       
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('asur.task', 'doc_id');
    }
}
