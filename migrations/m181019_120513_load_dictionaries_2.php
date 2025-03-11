<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m181019_120513_load_dictionaries_2
 */
class m181019_120513_load_dictionaries_2 extends Migration
{
    private $tableName = 'dictionaries';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = <<<CSV
Ultrasoundorgansystem;Система органов;глаза
Ultrasoundorgansystem;Система органов;мочевыделительная система
Ultrasoundorgansystem;Система органов;печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта
Ultrasoundorgansystem;Система органов;репродуктивная система самки
Ultrasoundorgansystem;Система органов;репродуктивная система самца
Investigationarea;Зона исследования;голова
Investigationarea;Зона исследования;отделы конечностей (сустав, регион конечности)
Investigationarea;Зона исследования;шейный
Investigationarea;Зона исследования;грудной
Investigationarea;Зона исследования;поясничный
Investigationarea;Зона исследования;крестцово-тазовый отделы
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        if (empty($items)) {
            Console::output(Console::ansiFormat('Error parsing CSV', [Console::FG_RED]));

            return false;
        }

        $this->resetSequences();

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
        $this->resetSequences();
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

    private function resetSequences()
    {
        $tables = [
            $this->tableName,
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            $this->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
    }
}
