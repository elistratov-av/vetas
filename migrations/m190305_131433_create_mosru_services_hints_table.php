<?php

use yii\db\Migration;

/**
 * Handles the creation of table `mosru_services_hints`.
 */
class m190305_131433_create_mosru_services_hints_table extends Migration
{
    protected $data = [
        "Биохимические исследования крови" => "Перед взятием крови для животного рекомендована голодная диета не менее 12 часов.",
        "Вакцинация" => "За 10 календарных дней до предполагаемой даты вакцинации животное должно быть обработано против гельминтов.",
        "Кастрация, стерилизация" => "Перед проведением хирургического вмешательства для животного рекомендована голодная диета не менее 12 часов.",
        "Общий анализ мочи" => "Рекомендовано доставить в лабораторию пробу мочи животного не позднее, чем через 3-4 часа после сбора материала.",
        "Общий клинический анализ крови" => "Перед взятием крови для животного рекомендована голодная диета не менее 12 часов.",
        "Определение гормонов в сыворотке крови" => "Перед взятием крови для животного рекомендована голодная диета не менее 12 часов.",
        "Содержание животных (зоогостиница)" => "Животное обязательно должно быть вакцинировано против бешенства. Собаки должны быть вакцинированы также и против лептоспироза.",
        "Травматология" => "Перед проведением хирургического вмешательства для животного рекомендована голодная диета не менее 12 часов.",
        "Экспресс-диагностика" => "Перед взятием крови для животного рекомендована голодная диета не менее 12 часов.",
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('mosru.services_hints', [
            'id' => $this->primaryKey(),
            'id_service' => $this->integer()->notNull(),
            'text' => $this->string(1024)
        ]);

        $this->addForeignKey(
            'fk-mosru_services_hints-id_service',
            'mosru.services_hints',
            'id_service',
            'gov_services',
            'id',
            'CASCADE'
        );

        $this->update(
            'gov_services',
            ['name' => 'Стрижка животных'],
            ['name' => 'Стрижка собак и кошек', 'type' => 'mosru']
        );

        foreach ($this->data as $name => $hint) {
            $this->insert('mosru.services_hints', [
                'id_service' => \app\models\db\GovServices::findOne(['name' => $name, 'type' => 'mosru'])->id,
                'text' => $hint
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-mosru_services_hints-id_service', 'mosru.services_hints');
        $this->dropTable('mosru.services_hints');
    }
}
