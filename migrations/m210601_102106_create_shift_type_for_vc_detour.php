<?php

use app\models\db\ShiftType;
use yii\db\Migration;

/**
 * Class m210601_102106_create_shift_type_for_vc_detour
 */
class m210601_102106_create_shift_type_for_vc_detour extends Migration
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
                'type' => 'DETOUR',
                'idle' => false,
                'description' => 'Обход',
                'colour' => '#81d9a7'
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $workDay = ShiftType::findOne(['description' => 'Обход']);
        if ($workDay) {
            $workDay->delete();
        }
    }
}
