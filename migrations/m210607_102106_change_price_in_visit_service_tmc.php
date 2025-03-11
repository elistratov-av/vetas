<?php

use yii\db\Migration;

/**
 * Class m210607_102106_change_price_in_visit_service_tmc
 */
class m210607_102106_change_price_in_visit_service_tmc extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('visit_service_tmc', 'price', $this->decimal(11, 2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('visit_service_tmc', 'price', $this->decimal(8, 2));
    }
}
