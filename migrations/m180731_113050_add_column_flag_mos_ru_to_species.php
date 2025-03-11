<?php

use yii\db\Migration;

/**
 * Class m180731_113050_add_column_flag_mos_ru_to_species
 */
class m180731_113050_add_column_flag_mos_ru_to_species extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE species ADD COLUMN IF NOT EXISTS flag_mos_ru BOOLEAN DEFAULT false;');
        $this->execute("COMMENT ON COLUMN species.flag_mos_ru IS 'Флаг: отображать для записи на mos.ru'");

        $this->execute("UPDATE species SET flag_mos_ru=TRUE WHERE name IN ('кошки', 'собаки')");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE gov_services DROP COLUMN IF EXISTS flag_mos_ru;');
    }

}
