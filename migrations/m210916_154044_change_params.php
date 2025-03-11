<?php

use app\commands\migrate\Migration;

/**
 * Class m210916_154044_change_params
 */
class m210916_154044_change_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('params', ['name' => 'мочевина (Ммоль/л) - результат исследований'], ['tech_name' => 'P26_Mochevinammvalue']);
        $this->update('params', ['name' => 'креатинин (Ммоль/л) - результат исследований'], ['tech_name' => 'P30_Creatininemcmvalue']);
        $this->update('params', ['name' => 'глюкоза (Ммоль/л) - результат исследований'], ['tech_name' => 'P40_Glukozamcmvalue']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('params', ['name' => 'мочевина (ммоль/л) - результат исследований'], ['tech_name' => 'P26_Mochevinammvalue']);
        $this->update('params', ['name' => 'креатинин (мкмоль/л) - результат исследований'], ['tech_name' => 'P30_Creatininemcmvalue']);
        $this->update('params', ['name' => 'глюкоза (мкмоль/л) - результат исследований'], ['tech_name' => 'P40_Glukozamcmvalue']);
    }
}
