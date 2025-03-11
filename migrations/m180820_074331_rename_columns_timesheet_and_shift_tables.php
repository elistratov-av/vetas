<?php

use yii\db\Migration;

/**
 * Class m180820_074331_rename_columns_timesheet_and_shift_tables
 */
class m180820_074331_rename_columns_timesheet_and_shift_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('timesheets','timing','date');
        $this->renameColumn('shifts','shift_type_id', 'id_type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180820_074331_rename_columns_timesheet_and_shift_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180820_074331_rename_columns_timesheet_and_shift_tables cannot be reverted.\n";

        return false;
    }
    */
}
