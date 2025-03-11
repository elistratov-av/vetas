<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_145122_set_zero_price_for_mosru_services
 */
class m190311_145122_set_zero_price_for_mosru_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('gov_services', ['price' => 0], ['type' => 'mosru']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
