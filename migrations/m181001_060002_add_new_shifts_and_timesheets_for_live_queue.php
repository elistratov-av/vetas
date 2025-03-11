<?php

use app\commands\migrate\Migration;
use app\models\db\{
    Shifts,
    ShiftType,
    Timesheets
};

/**
 * Class m181001_060002_add_new_shifts_and_timesheets_for_live_queue
 */
class m181001_060002_add_new_shifts_and_timesheets_for_live_queue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $shiftTypeWorkDay = ShiftType::find()->where(['type' => 'WORKDAY'])->one();
        $shiftTypeLiveQueue = ShiftType::find()->where(['type' => 'LIVE_QUEUE'])->one();
        $shifts = Shifts::find()->where(['id_type' => $shiftTypeWorkDay->id])->all();
        $shiftsId = [];
        foreach ($shifts AS $shift) {
            $shiftModelLiveQueue = new Shifts();
            $shiftModelLiveQueue->id_type = $shiftTypeLiveQueue->id;
            $shiftModelLiveQueue->name = "{$shift->name} (живая очередь)";
            $shiftModelLiveQueue->from_time = $shift->from_time;
            $shiftModelLiveQueue->duration = $shift->duration;
            $shiftModelLiveQueue->id_organization = $shift->id_organization;
            $shiftModelLiveQueue->save();
            if (is_numeric($shiftModelLiveQueue->id)) {
                $shiftsId[$shift->id] = $shiftModelLiveQueue->id;
            }
        }

        $timeSheets = Timesheets::find()->where(['id_shift' => array_keys($shiftsId)])->all();
        foreach ($timeSheets AS $timeSheet) {
            $timeSheetModelLiveQueue = new Timesheets();
            $timeSheetModelLiveQueue->id_specialist = $timeSheet->id_specialist;
            $timeSheetModelLiveQueue->id_shift = $shiftsId[$timeSheet->id_shift];
            $timeSheetModelLiveQueue->date = $timeSheet->date;
            $timeSheetModelLiveQueue->parent_id = $timeSheet->id;
            $timeSheetModelLiveQueue->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181001_060002_add_new_shifts_and_timesheets_for_live_queue cannot be reverted.\n";

        return false;
    }
}
