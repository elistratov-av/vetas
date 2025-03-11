<?php

use app\commands\migrate\Migration;

/**
 * Class m200907_095318_2986_update_visit_descriptions
 */
class m200907_095318_2986_update_visit_descriptions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            'public.description_types',
            ['sort_by' => 9],
            [
                'name' => 'Заключение',
                'entity_type' => 'visit',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(
            'public.description_types',
            ['sort_by' => null],
            [
                'name' => 'Заключение',
                'entity_type' => 'visit',
            ]
        );
    }
}
