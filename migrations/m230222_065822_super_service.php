<?php

use app\commands\migrate\Migration;

/**
 * Class m230222_065822_super_service
 */
class m230222_065822_super_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pets', 'mosru', $this->boolean()->defaultValue(true));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pets', 'mosru');
    }
}
