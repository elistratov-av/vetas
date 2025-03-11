<?php

use app\commands\migrate\Migration;

/**
 * Class m200901_130343_drop_table_service_types_description_types
 */
class m200901_130343_drop_table_service_types_description_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP VIEW public.visits_description_types');
        $this->dropTable('service_types_description_types');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
