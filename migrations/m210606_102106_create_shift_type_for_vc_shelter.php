<?php

use app\models\db\ShiftType;
use yii\db\Migration;

/**
 * Class m210606_102106_create_shift_type_for_vc_shelter
 */
class m210606_102106_create_shift_type_for_vc_shelter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $parent = ShiftType::findOne(['description' => 'Рабочий день']);

        if ($parent) {
            $this->insert('shift_type', [
                'parent_id' => $parent->id,
                'type' => 'SHELTER',
                'idle' => false,
                'description' => 'Выезд в приют',
                'colour' => '#61d9a7'
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $workDay = ShiftType::findOne(['description' => 'Выезд в приют']);
        if ($workDay) {
            $workDay->delete();
        }
    }
}
