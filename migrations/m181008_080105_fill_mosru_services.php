<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m181008_080105_fill_mosru_services
 */
class m181008_080105_fill_mosru_services extends Migration
{
    protected $serviceGoal = [
        1 => 'Лечение',
        2 => 'Лабораторно-диагностические исследования'
    ];

    protected $mosruServices = [
        [33, 1, 2, "Вакцинация", TRUE, TRUE, 1],
        [34, 1, 3,"Обрезка когтей", FALSE, TRUE, 1],
        [35, 1, 3,"Стрижка собак и кошек", FALSE, TRUE, 1],
        [36, 1, 7,"Катетеризация мочевого пузыря", FALSE, TRUE, 1],
        [37, 1, 3,"Санитарная стрижка и помывка", FALSE, TRUE, 1],
        [38, 1, 11, "Содержание животных (зоогостиница)", FALSE, TRUE, 1],
        [39, 1, 9,"Офтальмология", FALSE, TRUE, 1],
        [40, 1, 8,"Снятие зубного камня", FALSE, TRUE, 1],
        [41, 1, 7,"Обработка ран", FALSE, TRUE, 1],
        [42, 1, 7,"Травматология", FALSE, TRUE, 1],
        [43, 1, 7,"Блокады", FALSE, TRUE, 1],
        [44, 1, 7,"Купирование ушей", FALSE, TRUE, 1],
        [45, 1, 7,"Купирование хвоста", FALSE, TRUE, 1],
        [46, 1, 7,"Удаление прибылых пальцев", FALSE, TRUE, 1],
        [47, 1, 7,"Кастрация, стерилизация", FALSE, TRUE, 1],
        [48, 1, 6,"Чипирование", TRUE,  TRUE, 1],
        [49, 1, 1, "Консультация и лечебные манипуляции", FALSE, TRUE, 1],
        [50, 2, 4,"Эндоскопия", FALSE, TRUE, 1],
        [51, 2, 4,"ЭХОкардиограмма", FALSE, TRUE, 1],
        [52, 2, 4,"ЭКГ", FALSE, TRUE, 1],
        [53, 2, 4,"Отовидеоскопия", FALSE, TRUE, 1],
        [54, 2, 4,"УЗИ", FALSE, TRUE, 1],
        [55, 2, 4,"Люминесцентная диагностика", FALSE, TRUE, 1],
        [56, 2, 5,"Экспресс-диагностика", FALSE, TRUE, 1],
        [57, 2, 5,"Иные исследования", FALSE, TRUE, 2],
        [58, 2, 5,"Гельминтокопрологические исследования", FALSE, TRUE, 1],
        [59, 2, 5,"Исследование на кровепаразитарные болезни", FALSE, TRUE, 1],
        [60, 2, 5,"Определение гормонов в сыворотке крови", FALSE, TRUE, 1],
        [61, 2, 5,"Биохимические исследования крови", FALSE, TRUE, 1],
        [62, 2, 5,"Общий анализ кала", FALSE, TRUE, 1],
        [63, 2, 5,"Общий анализ мочи", FALSE, TRUE, 1],
        [64, 2, 5,"Отбор проб", TRUE,  TRUE, 1],
        [65, 1, 1, "Введение лекарственных препаратов", FALSE, TRUE, 1],
        [66, 1, 7,"Другие оперативные вмешательства", FALSE, TRUE, 1],
        [67, 2, 4,"Компьютерная томография", FALSE, TRUE, 1],
        [68, 2, 5,"Общий клинический анализ крови", FALSE, TRUE, 1],
        [69, 1, 12, "Оформление ветеринарных сопроводительных документов", FALSE, TRUE, 1],
        [70, 2, 4,"Рентген", FALSE, TRUE, 1],
        [71, 1, 8,"Удаление зубов", FALSE, TRUE, 1],
        [72, 1, 13,"Эвтаназия животных", FALSE, TRUE, 1]
    ];

    protected $mosruServicesGovServices = [
        [49, 3, FALSE],
        [34, 18, FALSE],
        [40, 92, FALSE],
        [46, 41, FALSE],
        [41, 47, FALSE],
        [47, 57, FALSE],
        [45, 67, FALSE],
        [45, 68, FALSE],
        [45, 69, FALSE],
        [44, 70, FALSE],
        [44, 71, FALSE],
        [44, 72, FALSE],
        [36, 74, FALSE],
        [36, 75, FALSE],
        [43, 77, FALSE],
        [42, 80, FALSE],
        [39, 99, FALSE],
        [63, 107, FALSE],
        [62, 108, FALSE],
        [61, 114, FALSE],
        [60, 139, FALSE],
        [59, 146, FALSE],
        [58, 147, FALSE],
        [57, 149, FALSE],
        [35, 178, FALSE],
        [37, 178, FALSE],
        [56, 151, FALSE],
        [55, 154, FALSE],
        [54, 156, FALSE],
        [53, 166, FALSE],
        [52, 167, FALSE],
        [51, 168, FALSE],
        [50, 169, FALSE],
        [38, 175, FALSE],
        [37, 181, FALSE],
        [33, 5, TRUE],
        [48, 35, TRUE],
        [64, 197, TRUE],
        [33, 173, TRUE],
        [48, 173, TRUE],
        [64, 173, TRUE],
        [47, 55, FALSE],
        [65, 6, FALSE],
        [66, 42, FALSE],
        [67, 160, FALSE],
        [68, 109, FALSE],
        [69, 210, FALSE],
        [70, 155, FALSE],
        [71, 95, FALSE],
        [72, 185, FALSE],
        [33, 5, FALSE],
        [64, 197, FALSE],
        [48, 35, FALSE]
    ];

    protected $serviceRating = [
        33 => 1, 
        69 => 2, 
        38 => 3,
        48 => 4,
        72 => 5,
        36 => 6,
        34 => 7,
        35 => 8,
        44 => 9,
        59 => 10,
        58 => 11, 
        53 => 12, 
        46 => 13,
        54 => 14,
        43 => 15,
        45 => 16,
        60 => 17,
        50 => 18,
        56 => 19,
        61 => 20,
        55 => 21, 
        41 => 22, 
        47 => 23,
        42 => 24,
        64 => 25,
        63 => 26,
        39 => 27,
        62 => 28,
        52 => 29,
        40 => 30,
        51 => 31, 
        68 => 32, 
        57 => 128,
        49 => null,
        37 => null
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->fillServiceGoal();
        $this->filleMosruServices();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

    protected function fillServiceGoal()
    {
        Console::output(Console::ansiFormat("Обновление целей", [Console::FG_GREEN]));
        $command = $this->db->createCommand("INSERT INTO service_goal(id, name) VALUES(:id, :name) 
          ON CONFLICT (id) DO UPDATE SET name = :name_upd");
        foreach ($this->serviceGoal as $id => $name) {
            $command->bindValues([
                'id' => $id,
                'name' => $name,
                'name_upd' => $name
            ])->execute();
        }
    }

    protected function filleMosruServices()
    {
        Console::output(Console::ansiFormat("Обновление услуг mos.ru", [Console::FG_GREEN]));
        $this->execute("DELETE FROM statistic.mosru_services_rating");
        $this->execute("DELETE FROM mosru_services_gov_services");
        $this->execute('DELETE FROM mosru_services');

        foreach ($this->mosruServices as $service) {
            $this->insert('mosru_services', [
                'id' => $service[0],
                'id_service_goal' => $service[1],
                'id_service_type' => $service[2],
                'name' => $service[3],
                'at_home' => $service[4],
                'at_clinic' => $service[5],
                'sort_by' => $service[6]
            ]);
        }

        foreach ($this->mosruServicesGovServices as $service) {
            $this->insert('mosru_services_gov_services', [
                'id_mosru_service' => $service[0],
                'id_gov_services' => $service[1],
                'at_home' => $service[2]
            ]);
        }

        Console::output(Console::ansiFormat("Обновление сортировки по услугам mos.ru", [Console::FG_GREEN]));
        foreach ($this->serviceRating as $mosru_services_id => $sort_by) {
            $this->insert('statistic.mosru_services_rating', [
                'mosru_services_id' => $mosru_services_id,
                'visits_count' => 0,
                'sort_by' => $sort_by
            ]);
        }
    }

}
