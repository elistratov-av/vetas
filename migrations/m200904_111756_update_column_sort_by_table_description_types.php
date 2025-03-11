<?php

use app\commands\migrate\Migration;

/**
 * Class m200904_111756_update_column_sort_by_table_description_types
 */
class m200904_111756_update_column_sort_by_table_description_types extends Migration
{

    private $tableName = 'description_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sequence = [
            'Дата заболевания' => 1,
            'Анамнез' => 2,
            'Клинические признаки' => 3,
            'Симптомы' => 4,
            'Предварительный диагноз' => 5,
            'Схема лечения' => 6,
            'Рекомендации' => 7,
            'Заключительный диагноз' => 8,
        ];

        foreach ($sequence as $name => $num) {
            $this->update(
                $this->tableName,
                ['sort_by' => $num],
                [
                    'name' => $name,
                    'entity_type' => 'visit',
                ]
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sequence = [
            'Дата заболевания' => null,
            'Анамнез' => 1,
            'Клинические признаки' => null,
            'Симптомы' => 2,
            'Предварительный диагноз' => 3,
            'Схема лечения' => 5,
            'Рекомендации' => null,
            'Заключительный диагноз' => 4,
        ];

        foreach ($sequence as $name => $num) {
            $this->update(
                $this->tableName,
                ['sort_by' => $num],
                [
                    'name' => $name,
                    'entity_type' => 'visit',
                ]
            );
        }
    }
}
