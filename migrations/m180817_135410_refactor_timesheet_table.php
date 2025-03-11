<?php

use yii\db\Migration;

/**
 * Class m180817_135410_refactor_timesheet_table
 */
class m180817_135410_refactor_timesheet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop old
        $this->dropColumn('timesheets', 'date');

        // Parent_id
        $this->addColumn('timesheets','parent_id', $this->integer());
        $this->addForeignKey('fk-timesheets_parent_id','timesheets','parent_id','timesheets','id');

        /**
         * Существующие записи в системе были имеют тип WORKDAY (via shift->shift_type),
         * однако для корректной работы надо хотя еще один дочерний интервал
         * По договоренности, этим интервалом будет "живая очередь"
         * Интервал введен в shifts (m180816_081725_refactor_shifts_table)
         *
         * Пользуясь тем, что он (кроме WORKDAY) там единственный тип shift
         * выбираем его как id_shift и дублируем с ним записи в timesheet


        $sql = '
INSERT INTO timesheets (
	parent_id, id_specialist, id_shift, timing,
	created_by, updated_by, created_at, updated_at)

SELECT 
	timesheets.id AS parent_id, 
	timesheets.id_specialist,
	sh2.id AS id_shift, 
	timesheets.timing,
	
	timesheets.created_by,
	timesheets.updated_by,
	timesheets.created_at,
	timesheets.updated_at
	
	  
FROM 
	timesheets
LEFT JOIN shifts 
	ON timesheets.id_shift = shifts.id
LEFT JOIN shifts sh2 
	ON shifts.duration = sh2.duration AND shifts.from_time = sh2.from_time AND shifts.shift_type_id <> sh2.shift_type_id
	';

        $this->execute($sql);
         */
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180817_135410_refactor_timesheet_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180817_135410_refactor_timesheet_table cannot be reverted.\n";

        return false;
    }
    */
}
