<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m190523_091226_load_violation_cancellation
 */
class m190523_091226_load_violation_cancellation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->loadViolationCancellation("violation_cancellation_20190522.csv");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->deleteViolationCancellation("violation_cancellation_20190522.csv");
    }

    /**
     * @param $file
     * @return array
     */
    protected function parseCsv($file)
    {
        $parseCsv = function ($handle) {
            $data = [];
            while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                if (!empty($row[0])) {
                    $data[] = $row;
                }
            }

            return $data;
        };

        $file = __DIR__ . "/data/{$file}";
        if (($handle = fopen($file, "r")) !== false) {
            $result = $parseCsv($handle);
        } else {
            Console::output(Console::ansiFormat("Файл {$file} не существует", [
                Console::FG_RED, Console::BOLD
            ]));
        }
        fclose($handle);

        return $result;
    }

    /**
     * @params $filename
     * @throws Exception
     * @throws Throwable
     */
    protected function loadViolationCancellation($filename)
    {
        Console::output(Console::ansiFormat("Load data to violation_cancellation", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $data = $this->parseCsv($filename);
        foreach ($data as $cancellation) {
            Console::output(Console::ansiFormat($cancellation[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->insert('violation_cancellation', [
                    'description' => $cancellation[0],
                ])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                throw $e;
            }
        }
    }

    /**
     * @param $filename
     */
    protected function deleteViolationCancellation($filename)
    {
        Console::output(Console::ansiFormat("Del data from violation_cancellation", [
            Console::FG_YELLOW, Console::BOLD,
        ]));

        $data = $this->parseCsv($filename);
        foreach ($data as $cancellation) {
            Console::output(Console::ansiFormat($cancellation[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->delete(
                    'violation_cancellation',
                    'description = :description',
                    [
                        'description' => $cancellation[0],
                    ]
                )->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190523_091226_load_violation_cancellation cannot be reverted.\n";

        return false;
    }
    */
}
