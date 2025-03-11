<?php

use app\commands\migrate\Migration;
use app\models\db\ServiceMeasures;

/**
 * Class m180921_082205_add_column_count_flag_to_service_measures_and_update
 */
class m180921_082205_add_column_count_flag_to_service_measures_and_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE service_measures ADD COLUMN IF NOT EXISTS count_flag BOOLEAN DEFAULT true;');
        $this->execute("COMMENT ON COLUMN service_measures.count_flag IS 'Флаг, указывающий на \"множественность\" услуги, измеряемой данной единицей измерения'");
        ServiceMeasures::updateAll(['count_flag' => false], ['name' => ['консультация', 'голова', 'исследование показателей', 'один выезд/один объект', 'система органов']]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE service_measures DROP COLUMN IF EXISTS count_flag;');
    }
}
