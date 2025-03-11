<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m181001_195757_load_dictionaries_values
 */
class m181001_195757_load_dictionaries_values extends Migration
{
    private $tableName = 'dictionaries';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = <<<CSV
Bacteria;Бактерии (цитологическое исслед.);кокки;;
Bacteria;Бактерии (цитологическое исслед.);палочки;;
Bacteria;Бактерии (цитологическое исслед.);грибковая микрофлора;;
Coprobilirubin;Билирубин (кал);наличие небольшого количества;;
Coprobilirubin;Билирубин (кал);наличие;;
Coprodor;Запах кала;специфический;;
Coprodor;Запах кала;гнилостный;;
Coprodor;Запах кала;зловонный;;
Coprodor;Запах кала;кислый;;
Coprodor;Запах кала;слабо выражен;;
Coprodor;Запах кала;резко выражен;;
Coprform;Консистенция кала;"отдельные твердые комки (""овечий кал"")";;
Coprform;Консистенция кала;плотный;;
Coprform;Консистенция кала;мягкий;;
Coprform;Консистенция кала;лентовидный;;
Coprform;Консистенция кала;жидкий;;
Coprform;Консистенция кала;кашицеобразный;;
Coprform;Консистенция кала;мазеобразный;;
Coprform;Консистенция кала;пенистый;;
Coprform;Консистенция кала;водянистый;;
Coprstarch;Крахмал (кал);наличие небольшого количества;;
Coprstarch;Крахмал (кал);наличие;;
Coprblood;Кровь (кал);наличие небольшого количества;;
Coprblood;Кровь (кал);наличие;;
Coprsoap;Мыла (кал);наличие небольшого количества;;
Coprsoap;Мыла (кал);наличие;;
Coprmuscledfibers;Мышечные волокна (кал);наличие небольшого количества;;
Coprmuscledfibers;Мышечные волокна (кал);наличие;;
Coprneutralfat;Нейтральный жир (кал);наличие небольшого количества;;
Coprneutralfat;Нейтральный жир (кал);наличие;;
Urintransparency;Прозрачность мочи;прозрачная;;
Urintransparency;Прозрачность мочи;мутная;;
Urintransparency;Прозрачность мочи;наличие примесей - с осадком;;
Urintransparency;Прозрачность мочи;наличие примесей - с хлопьями;;
Coprcontissuefibers;Соединительнотканные волокна (кал);наличие небольшого количества;;
Coprcontissuefibers;Соединительнотканные волокна (кал);наличие;;
Coprcolor;Цвет кала;белый;;
Coprcolor;Цвет кала;серовато-белый;;
Coprcolor;Цвет кала;золотисто-желтый;;
Coprcolor;Цвет кала;оранжево-светло-желтый;;
Coprcolor;Цвет кала;желтый;;
Coprcolor;Цвет кала;коричневый;;
Coprcolor;Цвет кала;темно-коричневый;;
Coprcolor;Цвет кала;коричнево-красный;;
Coprcolor;Цвет кала;темно-вишневый;;
Coprcolor;Цвет кала;зеленый;;
Coprcolor;Цвет кала;зеленовато-желтый;;
Coprcolor;Цвет кала;зеленовато-черный;;
Coprcolor;Цвет кала;черно-коричневый;;
Coprcolor;Цвет кала;черный;;
Urincolor;Цвет мочи;бесцветная;;
Urincolor;Цвет мочи;светло-желтый;;
Urincolor;Цвет мочи;янтарно-желтая;;
Urincolor;Цвет мочи;желтая;;
Urincolor;Цвет мочи;темно-желтая;;
Urincolor;Цвет мочи;оранжевая;;
Urincolor;Цвет мочи;коричневая;;
Urincolor;Цвет мочи;розовая;;
Urincolor;Цвет мочи;красная;;
Urincolor;Цвет мочи;голубая;;
Urincolor;Цвет мочи;зеленая;;
Abdomendeformation;Деформация брюшной полости (узи);не деформирована;;
Abdomendeformation;Деформация брюшной полости (узи);деформирована - увеличена в размере;;
Abdomendeformation;Деформация брюшной полости (узи);деформирована - уменьшена в размере;;
Abdomendeformation;Деформация брюшной полости (узи);деформирована - смещена в сторону органов;;
Urinarybladderdeformation;Мочевой пузырь: Деформация;не деформирован;;
Urinarybladderdeformation;Мочевой пузырь: Деформация;деформирован - увеличен в размере;;
Urinarybladderdeformation;Мочевой пузырь: Деформация;деформирован - уменьшен в размере;;
Urinarybladderdeformation;Мочевой пузырь: Деформация;деформирован - деформирован в области дорсальной стенки;;
Urinarybladderdeformation;Мочевой пузырь: Деформация;отсутствие визуализации;;
Parenchymatous;Эхогенность паренхимы (узи);норма;;
Parenchymatous;Эхогенность паренхимы (узи);повышена;;
Parenchymatous;Эхогенность паренхимы (узи);изоэхогенно;;
Parenchymatous;Эхогенность паренхимы (узи);гиперэхогенно;;
Parenchymatous;Эхогенность паренхимы (узи);гипоэхогенно;;
Parenchymatous;Эхогенность паренхимы (узи);анэхогенно;;
Parenchymatous;Эхогенность паренхимы (узи);не визуализируется;;
Periphvascularpattern;Периферический сосудистый рисунок (узи);не выражен;;
Periphvascularpattern;Периферический сосудистый рисунок (узи);слабо выражен;;
Periphvascularpattern;Периферический сосудистый рисунок (узи);выражен;;
Periphvascularpattern;Периферический сосудистый рисунок (узи);ярко выражен;;
Abdomendposition;Расположение брюшной полости (узи);анатомически правильное;;
Abdomendposition;Расположение брюшной полости (узи);анатомически не правильное;;
Kidneyposition;Расположение почек (узи);анатомически правильное;;
Kidneyposition;Расположение почек (узи);смещена краниально;;
Kidneyposition;Расположение почек (узи);смещена каудально;;
Alvuscontents;Содержимое полости (узи);гомогенное;;
Alvuscontents;Содержимое полости (узи);не гомогенное;;
Alvuscontents;Содержимое полости (узи);с дисперстным компонентом;;
Alvuscontents;Содержимое полости (узи);не визуализируется;;
Patternbile;Структура желчи (узи);однородная;;
Patternbile;Структура желчи (узи);не однородная;;
Patternbile;Структура желчи (узи);наличие гиперэхогенных образований подвижных;;
Patternbile;Структура желчи (узи);наличие гиперэхогенных образований неподвижных;;
Alvusstructure;Структура тела (узи);однородная;;
Alvusstructure;Структура тела (узи);не однородная;;
Alvusstructure;Структура тела (узи);наличие гиперэхогенных образований подвижных;;
Alvusstructure;Структура тела (узи);наличие гиперэхогенных образований неподвижных;;
Alvusstructure;Структура тела (узи);не визуализируется;;
Abdomendform;Форма брюшной полости (желчный пузырь);анатомически правильная;;
Abdomendform;Форма брюшной полости (желчный пузырь);не деформирована;;
Abdomendform;Форма брюшной полости (желчный пузырь);деформирована - увеличена в размере;;
Abdomendform;Форма брюшной полости (желчный пузырь);деформирована - уменьшена в размере;;
Abdomendform;Форма брюшной полости (желчный пузырь);деформирована - смещена в сторону органов;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);норма;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);повышена;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);понижена;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);изоэхогенно;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);гиперэхогенно;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);гипоэхогенно;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);анэхогенно;;
Abdomendechogenicity;Эхогенность брюшной полости (узи);не визуализируется;;
Kidneyechogenicity;Эхогенность почек (узи);однородная;;
Kidneyechogenicity;Эхогенность почек (узи);не однородная;;
Kidneyechogenicity;Эхогенность почек (узи);норма;;
Kidneyechogenicity;Эхогенность почек (узи);повышена;;
Kidneyechogenicity;Эхогенность почек (узи);понижена;;
Kidneyechogenicity;Эхогенность почек (узи);изоэхогенно;;
Kidneyechogenicity;Эхогенность почек (узи);гиперэхогенно;;
Kidneyechogenicity;Эхогенность почек (узи);гипоэхогенно;;
Kidneyechogenicity;Эхогенность почек (узи);анэхогенно;;
Kidneyechogenicity;Эхогенность почек (узи);не визуализируется;;
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        if (empty($items)) {
            Console::output(Console::ansiFormat('Error parsing CSV', [Console::FG_RED]));

            return false;
        }

        $this->cleanup();

        $created_at = date('Y-m-d H:i:s');

        foreach ($items as $item) {
            $type = strtolower(trim($item[0]));
            $name = trim($item[2]);

            $columns = compact('name', 'type', 'created_at');

            Console::output(Console::ansiFormat('Creating record [' . $type . ': ' . $name . ']', [Console::FG_YELLOW]));

            $this->db
                ->createCommand()
                ->insert($this->tableName, $columns)
                ->execute();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->cleanup();
    }

    private function cleanup()
    {
        $this->truncateTable($this->tableName);
        $this->db->createCommand("SELECT pg_catalog.setval('{$this->tableName}_id_seq', 1, false);")->execute();
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
