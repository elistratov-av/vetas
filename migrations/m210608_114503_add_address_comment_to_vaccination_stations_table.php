<?php

use app\commands\migrate\Migration;

/**
 * Class m210608_114503_add_address_comment_to_vaccination_stations_table
 */
class m210608_114503_add_address_comment_to_vaccination_stations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.vaccination_stations', 'address_comment', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.vaccination_stations', 'address_comment');
    }
}
