<?php

use yii\db\Migration;

/**
 * Class m180711_064458_add_alternative_name_col_to_gov_service
 */
class m180711_064458_add_alternative_name_col_to_gov_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE gov_services ADD COLUMN IF NOT EXISTS flag_mos_ru BOOLEAN DEFAULT false;');
        $this->execute("COMMENT ON COLUMN gov_services.flag_mos_ru IS 'Флаг: отображать для записи на mos.ru'");

        $this->execute('ALTER TABLE gov_services ADD COLUMN IF NOT EXISTS alternative_name character varying(100);');
        $this->execute("COMMENT ON COLUMN gov_services.alternative_name IS 'Имя для отображения на mos.ru'");

        $this->execute('UPDATE gov_services SET "alternative_name"="name";');
        $this->execute('UPDATE gov_services SET "flag_mos_ru"=true;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE gov_services DROP COLUMN IF EXISTS flag_mos_ru;');
        $this->execute('ALTER TABLE gov_services DROP COLUMN IF EXISTS alternative_name;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180711_064458_add_alternative_name_col_to_gov_service cannot be reverted.\n";

        return false;
    }
    */
}
