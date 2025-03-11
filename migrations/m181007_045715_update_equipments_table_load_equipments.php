<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m181007_045715_update_equipments_table_load_equipments
 */
class m181007_045715_update_equipments_table_load_equipments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->resetSequences();
        $this->loadTmcTypes();
        $this->loadEquipments();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->resetSequences();
    }

    private function loadTmcTypes()
    {
        $csv = <<<CSV
анализатор мочи;;;;;;;;;
анализатор электролитов;;;;;;;;;
аппарат гистологической проводки карусельного типа;;;;;;;;;
биохимический анализатор крови;;;;;;;;;
весы;;;;;;;;;
гематологический анализатор крови;;;;;;;;;
гематологический и биохимический анализатор крови IDEXX;;;;;;;;;
глюкометр;;;;;;;;;
ИВЛ;;;;;;;;;
кислородный концентратор;;;;;;;;;
компьютерный томограф;;;;;;;;;
лампа Вуда;;;;;;;;;
машинка для стрижки шерсти;;;;;;;;;
микроскоп;;;;;;;;;
монитор наблюдения за функциональным состоянием пациента;;;;;;;;;
отоскоп;;;;;;;;;
офтальмомикроскоп;;;;;;;;;
офтальмоскоп;;;;;;;;;
рентгеновский аппарат;;;;;;;;;
скалер;;;;;;;;;
сканер;;;;;;;;;
тонометр;;;;;;;;;
УЗИ;;;;;;;;;
физиотерапевтическое оборудование;;;;;;;;;
электрокардиограф;;;;;;;;;
эндоскоп;;;;;;;;;
дезинфекционное оборудование;;;;;;;;;
крематор;;;;;;;;;
автомобиль неотложной ветеринарной помощи;;;;;;;;;
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        if (empty($items)) {
            Console::output(Console::ansiFormat('Error parsing CSV tmc_types', [Console::FG_RED]));

            return;
        }

        $created_at = date('Y-m-d H:i:s');

        foreach ($items as $i => $item) {
            $name = trim($item[0]);
            if (empty($name)) {
                Console::output(Console::ansiFormat('Empty tmc_type name: line ' . $i, [Console::FG_RED]));
                continue;
            }
            $tmc_class = 'equipment';
            $columns = compact('name', 'tmc_class');
            $exists = (new Query())
                ->from('tmc_types')
                ->where($columns)
                ->exists();
            if ($exists !== true) {
                Console::output(Console::ansiFormat('Creating tmc_type [' . $tmc_class . ': ' . $name . ']', [Console::FG_YELLOW]));
                $columns['created_at'] = $created_at;
                $this->db
                    ->createCommand()
                    ->insert('tmc_types', $columns)
                    ->execute();
            }
        }
    }

    private function loadEquipments()
    {
        $csv = <<<CSV
анализатор мочи;DocUReader
анализатор мочи;Urisis
анализатор мочи;Урискан
анализатор электролитов;IMS
аппарат гистологической проводки карусельного типа;histomaster
биохимический анализатор крови;Chemray
биохимический анализатор крови;Reflotron Plus
биохимический анализатор крови;VegaSys
биохимический анализатор крови;Stat Fax
биохимический анализатор крови;Stat Fax 1904
биохимический анализатор крови;Stat Fax 303
биохимический анализатор крови;Stat Fax 3300
весы;CAS15OAS
весы;Momert 6681
весы;Весы напольные
весы;ВРЛ-200
весы;Ладога
весы;ТВ-М-150
гематологический анализатор крови;Abacus Junior Vet
гематологический анализатор крови;Mindray BC 2800 Vet
гематологический анализатор крови;Mythic-18 Vet
гематологический анализатор крови;Rayto
гематологический анализатор крови;RT-7600
гематологический и биохимический анализатор крови IDEXX;Laser Cyte USA IDEXX
гематологический и биохимический анализатор крови IDEXX;VetTest8008 IVLS PC Station
глюкометр;CardioChek P*А c набором тест полосок
глюкометр;One Touch Ultra Easy
ИВЛ;АДР 1200
кислородный концентратор;АРМЕД 7F-1L
кислородный концентратор;АРМЕД 7F-5L
кислородный концентратор;АРМЕД 7F-8L
компьютерный томограф;ASTEION Super 4 Toshiba
лампа Вуда;Лампа Вуда
лампа Вуда;ОЛДД-01
лампа Вуда;САПФИР
машинка для стрижки шерсти;MOSER MAX-45
машинка для стрижки шерсти;MOSER REX
машинка для стрижки шерсти;Машинка для стрижки
микроскоп;800 М
микроскоп;Micmed 2
микроскоп;Micmed 5
микроскоп;MicroOptix 100
микроскоп;Micros
микроскоп;Nikon Eclipse E200 LED MV
микроскоп;БИМАМ Р-11
микроскоп;Микроскоп бинокулярный
микроскоп;Бинокулярный XS 910
микроскоп;Бинокулярный с осветителем
микроскоп;Видеомикроскоп бинокулярный МС100
микроскоп;Микроскоп МС-50
микроскоп;Минимед
микроскоп;Олимпус
микроскоп;Олимпус СХ 31
микроскоп;Петролазер
микроскоп;Студар
микроскоп;Хирургический Имани
монитор наблюдения за функциональным состоянием пациента;Dixion Storm 5770
монитор наблюдения за функциональным состоянием пациента;Horizon 1000
монитор наблюдения за функциональным состоянием пациента;Mediana
монитор наблюдения за функциональным состоянием пациента;SCHILLER ARGUS LCM
монитор наблюдения за функциональным состоянием пациента;Zoomed AM-10
монитор наблюдения за функциональным состоянием пациента;Монитор анестезиолога-реаниматолога компьютерный для гемодинамического мониторинга
отоскоп;Riester UNI-1
отоскоп;Welch Allyn
отоскоп;Видеоотоскоп
отоскоп;Отоскоп
офтальмомикроскоп;Офтальмоскоп-отоскоп
офтальмоскоп;3001 ri-mini 2.5 V
офтальмоскоп;Basic Set-Combilight C10/Eurolight E10 Ka We
офтальмоскоп;Heine mini 3000
офтальмоскоп;Welch Allyn
офтальмоскоп;ВETA 200 HEINE
офтальмоскоп;Гониоскоп по Ван-Бойнингену
офтальмоскоп;Криоаппарат миниатюрный CryoPen
офтальмоскоп;Лампа Щелевая
офтальмоскоп;Офтальмоскоп
офтальмоскоп;Офтальмоскоп налобный бинокулярный
офтальмоскоп;Офтальмоскоп-оттоскоп Riester
офтальмоскоп;Факоэмульсификатор Оптимед
рентгеновский аппарат;10Л6-01
рентгеновский аппарат;ASKOT TOP-100
рентгеновский аппарат;EcoTron EPX-F3200
рентгеновский аппарат;Flexavision Shimadzu НВ
рентгеновский аппарат;ICRco Chrome LF
рентгеновский аппарат;Mobil 1000
рентгеновский аппарат;OPERA RT 20
рентгеновский аппарат;Orange
рентгеновский аппарат;Philips Practix 400
рентгеновский аппарат;SIEMENS Polymobil plus
рентгеновский аппарат;Аппарат рентгеновский портативный
рентгеновский аппарат;МСА GMM
рентгеновский аппарат;Практик - 400
рентгеновский аппарат;С-дуга ТЕCHNIK-TCA
скалер;EMS Piezon Master 400
скалер;Micropiezo S
скалер;Multipieso Mectron
скалер;Ultra LED
скалер;USD-E LED
скалер;Woodpecker DTE-D7
скалер;Woodpecker UDS L
скалер;Аппарат для снятия зубного камня
сканер;ANIMAL-ID MICROVEN
сканер;FX-PET-EL 2010
сканер;Globalvet
сканер;ISO max
сканер;Считыватель микрочипов стандарта ISO 11784/5 для идентификации животных. Модель CH R 04
тонометр;Tonovet Icare
тонометр;тонометрглазного давления
УЗИ;Сономед
УЗИ;ALOKA Prosound 2
УЗИ;ALOKA Prosound ALPHA 6 Premier
УЗИ;ECHO Blaster 128 EXT
УЗИ;FUKUDA Denshi FFsonic UF-4000
УЗИ;FUKUDA Denshi FFsonic UF-4100
УЗИ;HITACHI ALOKA F 37
УЗИ;HITACHI ALOKA Premier Alpha 6
УЗИ;HITACHI ALOKA SSD 1400
УЗИ;HONDA-HS-1500
УЗИ;HONDA-HS-2000
УЗИ;Sonix OP/SP/MDP
УЗИ;SonoScape S20Pro
УЗИ;UF 750 XT
УЗИ;Woodpеcker UDS-L
физиотерапевтическое оборудование;Interferential IF-7P
физиотерапевтическое оборудование;Аппарат для лечения током надтональной частоты Ультратон
физиотерапевтическое оборудование;"Аппарат для магнитно-инфракрасной лазерной терапии ""Милта"""
физиотерапевтическое оборудование;Аппарат для УВЧ-терапии УВЧ-30
физиотерапевтическое оборудование;Аппарат лазерной терапии Матрикс с излуч. головками
физиотерапевтическое оборудование;"Аппарат лазерный физиотерапевтический ""Милта Ф-8-01"""
физиотерапевтическое оборудование;Аппарат магнитно-инфракрасный лазерный Рикта 04/4
физиотерапевтическое оборудование;"Аппарат ""Мустанг-2000+"""
физиотерапевтическое оборудование;Аппарат УВЧ
физиотерапевтическое оборудование;Беговая дорожка для животных
физиотерапевтическое оборудование;Вибромассажер УУР-7
физиотерапевтическое оборудование;лампа Биоптрон
электрокардиограф;Biocare ECG-300G
электрокардиограф;Fucuda FX 7102
электрокардиограф;KENZ ECG 01
электрокардиограф;Shiller CARDIOVIT AT-1
электрокардиограф;Shiller CARDIOVIT AT-104 HC
электрокардиограф;АТ-1 Vet
электрокардиограф;ФТ-1 Vet
эндоскоп;Гастрофиброскоп
эндоскоп;Гастрофиброскоп CLK-4 OLIMPUS
эндоскоп;Колоноскоп
эндоскоп;"Лапараскоп ""Тол-80"""
эндоскоп;Ларингоскоп
эндоскоп;ларингоскоп EFFNER
эндоскоп;Ларингоскоп модель 320 (Ларингоскоп детский)
эндоскоп;Ректоскоп
дезинфекционное оборудование;ДУК-2 на базе автомобиля ГАЗ
дезинфекционное оборудование;ВДМ на базе автомобиля УАЗ
дезинфекционное оборудование;ДУ Унигрин (электро, бензин)
дезинфекционное оборудование;Опрыскиватель Цефарелли
дезинфекционное оборудование;ДУ 750
дезинфекционное оборудование;Аэрозольная установка Хайфог
дезинфекционное оборудование;Генератор холодного тумана Шторм
дезинфекционное оборудование;Ранцевый распылитель
дезинфекционное оборудование;Опрыскиватель Фармат
дезинфекционное оборудование;Опрыскиватель СОЛО
дезинфекционное оборудование;ДУ АББА
дезинфекционное оборудование;Генераторой горячего тумана TF-35
крематор;крематор
автомобиль неотложной ветеринарной помощи;автомобиль неотложной ветеринарной помощи
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        if (empty($items)) {
            Console::output(Console::ansiFormat('Error parsing CSV equipments', [Console::FG_RED]));

            return;
        }

        $rows = (new Query())
            ->from('tmc_types')
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $tmc_types = \yii\helpers\ArrayHelper::map($rows, 'id', 'name');

        $created_at = date('Y-m-d H:i:s');

        foreach ($items as $i => $item) {
            $tmc_type_name = trim($item[0]);
            if (empty($tmc_type_name)) {
                Console::output(Console::ansiFormat('Empty equipment tmc_type_name: line ' . $i, [Console::FG_RED]));
                continue;
            }
            $name = trim($item[1]);
            if (empty($name)) {
                Console::output(Console::ansiFormat('Empty equipment name: line ' . $i, [Console::FG_RED]));
                continue;
            }

            $id_tmc_type = array_search($tmc_type_name, $tmc_types);
            if ($id_tmc_type === false) {
                Console::output(Console::ansiFormat('TMC type [' . $tmc_type_name . '] not found for equipment ' . $name, [Console::FG_RED]));
                continue;
            }

            $columns = compact('name', 'id_tmc_type');
            $exists = (new Query())
                ->from('equipments')
                ->where($columns)
                ->exists();
            if ($exists !== true) {
                Console::output(Console::ansiFormat('Creating equipment [' . $name . ']', [Console::FG_YELLOW]));
                $columns['created_at'] = $created_at;
                $this->db
                    ->createCommand()
                    ->insert('equipments', $columns)
                    ->execute();
            }
        }
    }

    private function resetSequences()
    {
        $tables = [
            'tmc_types',
            'tmc',
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            $this->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
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
