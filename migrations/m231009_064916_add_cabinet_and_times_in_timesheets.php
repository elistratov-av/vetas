<?php

use app\commands\migrate\Migration;

/**
 * Class m231009_064916_add_cabinet_and_times_in_timesheets
 */
class m231009_064916_add_cabinet_and_times_in_timesheets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('timesheets', 'times', $this->text());
        $this->addColumn('timesheets', 'cabinet_type', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('timesheets', 'times');
        $this->dropColumn('timesheets', 'cabinet_type');
    }
}
