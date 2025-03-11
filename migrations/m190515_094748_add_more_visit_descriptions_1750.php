<?php

use app\commands\migrate\Migration;
use app\models\db\DescriptionTypes;
use app\models\db\ServiceTypes;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m190515_094748_add_more_visit_descriptions_1750
 */
class m190515_094748_add_more_visit_descriptions_1750 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $data = [
            'Терапия' => [
                'Анамнез',
                'Симптомы',
                'Предварительный диагноз',
                'Заключительный диагноз',
                'Схема лечения',
                'Рекомендации',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
            'Вакцинация' => [
                'Анамнез',
                'Симптомы',
                'Рекомендации',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
            'Груминг' => [
                'Анамнез',
                'Симптомы',
                'Рекомендации',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
            'Хирургия' => [
                'Анамнез',
                'Симптомы',
                'Предварительный диагноз',
                'Заключительный диагноз',
                'Схема лечения',
                'Рекомендации',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
            'Стоматология' => [
                'Анамнез',
                'Симптомы',
                'Предварительный диагноз',
                'Заключительный диагноз',
                'Схема лечения',
                'Рекомендации',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
            'Офтальмология' => [
                'Анамнез',
                'Симптомы',
                'Предварительный диагноз',
                'Заключительный диагноз',
                'Схема лечения',
                'Рекомендации',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
            'Оказание услуг на дому' => [
                'Анамнез',
                'Симптомы',
                'Предварительный диагноз',
                'Заключительный диагноз',
                'Схема лечения',
                'Рекомендации',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
            'Лабораторные исследования' => [
                'Анамнез',
                'Заключение',
                'Дата заболевания',
                'Дополнительные исследования',
                'Клинические признаки',
                'Лечебная помощь',
            ],
        ];

        $serviceTypes = ServiceTypes::find()
            ->asArray()
            ->indexBy('name')
            ->all();

        $descriptionTypes = DescriptionTypes::find()
            ->where(['entity_type' => 'visit'])
            ->asArray()
            ->indexBy('name')
            ->all();

        foreach ($data as $serviceTypeName => $items) {
            if (!array_key_exists($serviceTypeName, $serviceTypes)) {
                $serviceType = new ServiceTypes([
                    'name' => $serviceTypeName,
                ]);
                Console::output('Creating service_type ' . $serviceTypeName . '...');
                if ($serviceType->save()) {
                    $serviceTypes[$serviceTypeName] = $serviceType->toArray();
                    Console::output('   - ОК');
                } else {
                    Console::output('   - error');
                    continue;
                }
            }
            $serviceTypeId = $serviceTypes[$serviceTypeName]['id'];
            $ids = [];
            foreach ($items as $descriptionTypeName) {
                if (!array_key_exists($descriptionTypeName, $descriptionTypes)) {
                    $descriptionType = new DescriptionTypes([
                        'name' => $descriptionTypeName,
                        'entity_type' => 'visit',
                    ]);
                    Console::output('Creating description_type ' . $descriptionTypeName . '...');
                    if ($descriptionType->save()) {
                        $descriptionTypes[$descriptionTypeName] = $descriptionType->toArray();
                        Console::output('   - ОК');
                    } else {
                        Console::output('   - error');
                        continue;
                    }
                }
                $descriptionTypeId = $descriptionTypes[$descriptionTypeName]['id'];
                $ids[] = $descriptionTypeId;
                $link = (new Query())
                    ->from('service_types_description_types')
                    ->where([
                        'id_service_type' => $serviceTypeId,
                        'id_description_type' => $descriptionTypeId,
                    ])
                    ->exists();
                if (empty($link)) {
                    Console::output('Linking description_type ' . $descriptionTypeName . ' to service_type ' . $serviceTypeName);
                    $this->db
                        ->createCommand()
                        ->insert(
                            'service_types_description_types',
                            [
                                'id_service_type' => $serviceTypeId,
                                'id_description_type' => $descriptionTypeId,
                            ]
                        )
                        ->execute();
                }
            }
            if (!empty($ids)) {
                $this->db
                    ->createCommand()
                    ->delete(
                        'service_types_description_types',
                        [
                            'and',
                            ['id_service_type' => $serviceTypeId],
                            ['not in', 'id_description_type', $ids],
                        ]
                    )
                    ->execute();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190515_094748_add_more_visit_descriptions_1750 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190515_094748_add_more_visit_descriptions_1750 cannot be reverted.\n";

        return false;
    }
    */
}
