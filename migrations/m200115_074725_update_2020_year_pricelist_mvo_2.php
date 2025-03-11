<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;

/**
 * Class m200115_074725_update_2020_year_pricelist_mvo_2
 */
class m200115_074725_update_2020_year_pricelist_mvo_2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(GovServices::tableName(), ['cod' => '0394'], ['and', ['cod' => '0393'], ['name' => 'Измерение артериального давления (тонометрия)']]);
        $this->update(GovServices::tableName(), ['cod' => '0393'], ['and', ['cod' => '0394'], ['name' => 'Диспансеризация']]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200115_074725_update_2020_year_pricelist_mvo_2 cannot be reverted.\n";

        return false;
    }
}
