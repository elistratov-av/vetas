<?php

use app\commands\migrate\Migration;

/**
 * Class m190409_164835_add_ambulance_shift_type
 */
class m190409_164835_add_ambulance_shift_type extends Migration
{
    private $tableName = 'shift_type';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%' . $this->tableName . '}}', [
            'parent_id' => 1,
            'type' => 'AMBULANCE',
            'idle' => false,
            'description' => 'Выезд на дом (НВП)',
            'colour' => '#6859b3',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%' . $this->tableName . '}}', [
            'type' => 'AMBULANCE',
        ]);
    }
}
