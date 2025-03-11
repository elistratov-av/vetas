<?php

use app\models\db\ShiftType;
use yii\db\Migration;

/**
 * Class m210420_102106_create_shift_type_for_vc
 */
class m210420_102106_create_shift_type_for_vc extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $workDay = ShiftType::findOne(['description' => 'Рабочий день']);

        if ($workDay) {
            $this->insert('shift_type', [
                'parent_id' => $workDay->id,
                'type' => 'VACCINATION_STATION',
                'idle' => false,
                'description' => 'Прививочный пункт',
                'colour' => '#80d6a7'
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $workDay = ShiftType::findOne(['description' => 'Прививочный пункт']);
        if ($workDay) {
            $workDay->delete();
        }
    }
}
