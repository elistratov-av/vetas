<?php

use app\commands\migrate\Migration;

/**
 * Class m180921_062540_add_column_count_flag_to_service_measures
 */
class m180921_062540_add_column_count_flag_to_service_measures extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE service_types ADD COLUMN IF NOT EXISTS count_flag BOOLEAN DEFAULT false;');
        $this->execute("COMMENT ON COLUMN service_types.count_flag IS 'Флаг, указывающий на \"множественность\" услуги, измеряемой данной единицей измерения'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE service_types DROP COLUMN IF EXISTS count_flag;');
    }
}
