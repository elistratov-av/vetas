<?php

use app\commands\migrate\Migration;

/**
 * Class m230213_041338_add_service_type
 */
class m230213_041338_add_service_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('public.service_types', [
            'name' => 'Телеветеренария'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('public.service_types', ['name' => 'Телеветеренария']);
    }
}
