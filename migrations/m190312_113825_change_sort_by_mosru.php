<?php

use app\commands\migrate\Migration;

/**
 * Class m190312_113825_change_sort_by_mosru
 */
class m190312_113825_change_sort_by_mosru extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            'gov_services',
            ['sort_by' => 1024],
            [
                'type' => 'mosru',
                'name' => ['Иные исследования', 'Другие оперативные вмешательства']
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(
            'gov_services',
            ['sort_by' => 1],
            [
                'type' => 'mosru',
                'name' => ['Иные исследования', 'Другие оперативные вмешательства']
            ]
        );
    }
}
