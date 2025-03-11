<?php

use app\commands\migrate\Migration;

/**
 * Class m210630_141319_fias_addresses_add_col_bti_city_area_code
 */
class m210630_141319_fias_addresses_add_col_bti_city_area_code extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE public.fias_addresses ADD COLUMN IF NOT EXISTS bti_city_area_code character varying(8)[];');
        $this->execute("COMMENT ON COLUMN public.fias_addresses.bti_city_area_code IS 'Код административного округа'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'public.fias_addresses',
            'bti_city_area_code'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210630_141319_fias_addresses_add_col_bti_city_area_code cannot be reverted.\n";

        return false;
    }
    */
}
