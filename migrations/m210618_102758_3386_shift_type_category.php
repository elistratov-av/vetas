<?php

use app\commands\migrate\Migration;

/**
 * Class m210618_102758_3386_shift_type_category
 */
class m210618_102758_3386_shift_type_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('shift_type', 'cant_overlap');

        $this->addColumn(
            'shift_type',
            'overlap_category',
            $this->string(32)
        );
        $this->addCommentOnColumn(
            'shift_type',
            'overlap_category',
            'Категория. Используется для правил пересечений смен'
        );

        $this->update(
            'shift_type',
            ['overlap_category' => 'CLINIC'],
            [
                'IN', 'type',
                ['MOSRU_APPOINTMENT', 'PHONE_APPOINTMENT', 'LIVE_QUEUE']
            ]);

        $this->update(
            'shift_type',
            ['overlap_category' => 'AT_HOME'],
            [
                'IN', 'type',
                ['CALL_TO_HOME', 'AMBULANCE', 'MOSRU_CALL_TO_HOME']
            ]);

        $this->update(
            'shift_type',
            ['overlap_category' => 'EVENT'],
            [
                'IN', 'type',
                ['VACCINATION_STATION', 'DETOUR', 'SHELTER']
            ]);

        $this->update(
            'shift_type',
            ['overlap_category' => 'IDLE'],
            [
                'IN', 'type',
                ['BREAK']
            ]);

        $this->update(
            'shift_type',
            ['overlap_category' => 'TOP_LEVEL'],
            [
                'IN', 'type',
                ['WORKDAY', 'SICK_LEAVE', 'VACATION', 'HOLIDAY']
            ]);

        $this->execute("ALTER TABLE public.shift_type ALTER COLUMN overlap_category SET NOT NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'shift_type',
            'overlap_category'
        );

        // Воссанавливаем старый стобец
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

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210618_102758_3386_shift_type_category cannot be reverted.\n";

        return false;
    }
    */
}
