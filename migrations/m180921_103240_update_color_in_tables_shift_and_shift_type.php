<?php

use app\commands\migrate\Migration;
use app\models\db\ShiftType;
use app\models\db\Shifts;

/**
 * Class m180921_103240_update_color_in_tables_shift_and_shift_type
 */
class m180921_103240_update_color_in_tables_shift_and_shift_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $colors = [
            'MOSRU_APPOINTMENT' => '#dc7633',
            'PHONE_APPOINTMENT' => '#3498db',
            'LIVE_QUEUE' => '#b03a2e',
            'BREAK' => '#5d6d7e',
            'WORKDAY' => '#14cc8f',
            'SICK_LEAVE' => '#b6b9bf',
            'VACATION' => '#ffcc66',
            'HOLIDAY' => 'dadee6'
        ];

        $this->execute('DROP TRIGGER trigger_shift_type_insert_update_check ON public.shift_type;');
        $this->execute('DROP FUNCTION public.shift_type_insert_update_check()');

        foreach ($colors AS $type => $color) {
            ShiftType::updateAll(['colour' => $color], ['type' => $type]);
        }
        $shiftTypeTable = ShiftType::tableName();
        $this->alterColumn($shiftTypeTable, 'colour', 'SET NOT NULL');
        $this->execute("COMMENT ON COLUMN {$shiftTypeTable}.colour IS 'Цвет, всегда обязателен';");
        $this->dropColumn(Shifts::tableName(), 'colour');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180921_103240_update_color_in_tables_shift_and_shift_type cannot be reverted.\n";

        return false;
    }
}
