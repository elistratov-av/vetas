<?php

use app\commands\migrate\Migration;

/**
 * Class m200817_010100_fill_gov_services_for_broods_and_multiple
 */
class m200817_010100_fill_gov_services_for_broods_and_multiple extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = <<<CSV
"Консультация ветспециалиста";"Терапия";"ALL";"HEAD"
"Первичный клинический осмотр";"Терапия";"ALL";"HEAD"
"Повторный клинический осмотр";"Терапия";"ALL";"HEAD"
"Вакцинация животных с проведением клинического осмотра, консультации, инъекции";"Вакцинация";"HEAD";"HEAD"
"Введение лекарственных препаратов: внутримышечное, подкожное, внутрикожное, пероральное, глазное капельное, внутривенное через катетер";"Терапия";"HEAD";"HEAD"
"Обрезка когтей";"Груминг";"HEAD";"HEAD"
"Удаление колтунов";"Груминг";"HEAD";"HEAD"
"Удаление иксодовых клещей";"Груминг";"HEAD";"HEAD"
"Санация ушных раковин";"Груминг";"HEAD";"HEAD"
"Очистка параанальных желез";"Груминг";"HEAD";"HEAD"
"Взятие проб крови из вены";"Лабораторные исследования";"HEAD";"HEAD"
"Взятие проб крови из капилляра";"Лабораторные исследования";"HEAD";"HEAD"
"Взятие мазка отпечатка на цитологический анализ";"Лабораторные исследования";"HEAD";"HEAD"
"Пункционная биопсия на цитологический анализ";"Лабораторные исследования";"HEAD";"HEAD"
"Взятие соскобов, мазков, смывов для диагностических исследований";"Лабораторные исследования";"HEAD";"HEAD"
"Считывание номера микрочипа (сканирование)";"Терапия";"HEAD";"HEAD"
"Обработка против эктопаразитов";"Груминг";"HEAD";"HEAD"
"Обработка кожного покрова с санацией (туалетом) раневой поверхности";"Терапия";"HEAD";"HEAD"
"Обработка ран";"Хирургия";"HEAD";"HEAD"
"Перевязка ран";"Хирургия";"HEAD";"HEAD"
"Снятие швов";"Хирургия";"HEAD";"HEAD"
"Обработка послеоперационного шва";"Хирургия";"HEAD";"HEAD"
"Удаление зубов - молочных и постоянных у собак, грызунов";"Стоматология";"HEAD";"HEAD"
"Выезд для оказания ветеринарной помощи на дому";"Оказание услуг на дому";"ALL";"ALL"
"Транспортировка животного";"Оказание услуг на дому";"ALL";"ALL"
"Выезд ветврача";"Оказание услуг на дому";"ALL";"ALL"
"Взвешивание животных";"Терапия";"HEAD";"HEAD"
"Отбор проб для лабораторных исследований";"Лабораторные исследования";"HEAD";"HEAD"
"Клинический осмотр птиц, рыб, грызунов, рептилий, мелких животных и др.";"Терапия";"HEAD";"HEAD"
"Вакцинация";"Вакцинация";"HEAD";"HEAD"
"Консультация и лечебные манипуляции";"Терапия";"HEAD";"HEAD"
"Отбор проб";"Лабораторные исследования";"HEAD";"HEAD"
"Введение лекарственных препаратов";"Терапия";"HEAD";"HEAD"
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $name = $item[0];
            $for_broods = $item[2];
            $for_multiple = $item[3];

            $service = \app\models\db\GovServices::findOne(['name' => $name]);
            if ($service === null) {
                throw new \Exception($name . ' not found');
            }

            $service->updateAttributes(compact('for_broods', 'for_multiple'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200817_010100_fill_gov_services_for_broods_and_multiple cannot be reverted.\n";

        return false;
    }

    /**
     * @param string $csv
     * @param string $function
     * @return array
     * @throws \Exception
     */
    private function parseCsv($csv, $function)
    {
        $arr = [];
        $data = str_getcsv($csv, "\n");
        foreach ($data as $row) {
            $arr[] = str_getcsv($row, ';', '"');
        }

        if (empty($arr)) {
            throw new \Exception($function . ': Failed to parse CSV');
        }

        return $arr;
    }
}
