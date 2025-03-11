<?php

use app\commands\migrate\Migration;

/**
 * Class m210616_113228_3386_new_shift_type_call_to_home
 */
class m210616_113228_3386_new_shift_type_call_to_home extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('shift_type', [
            'parent_id' => 1,
            'type' => 'MOSRU_CALL_TO_HOME',
            'idle' => false,
            'description' => 'Выезд на дом (mos.ru)',
            'colour' => '#f700ff',
        ]);

        $this->addColumn('shift_type', 'cant_overlap', 'BOOLEAN DEFAULT false');

        $this->execute("COMMENT ON COLUMN shift_type.cant_overlap IS 'Флаг: не может пересекаться с другими  (исключение: с типом перерыв)'");

        $this->update(
            'shift_type',
            ['cant_overlap' => true],
            [
                'IN', 'type',
                ['SICK_LEAVE', 'VACATION', 'CALL_TO_HOME', 'HOLIDAY', 'AMBULANCE', 'VACCINATION_STATION', 'DETOUR', 'SHELTER', 'MOSRU_CALL_TO_HOME']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('shift_type', [
            'type' => 'MOSRU_CALL_TO_HOME'
        ]);

        $this->dropColumn('shift_type', 'cant_overlap');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210616_113228_3386_new_shift_type_call_to_home cannot be reverted.\n";

        return false;
    }
    */
}
