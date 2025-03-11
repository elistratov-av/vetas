<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m190515_120111_load_violations
 */
class m190515_120111_load_violations extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->loadViolationTypes('violation_types_20190517.csv');
        $this->loadViolationAdminRights('admin_rights_violation_20190517.csv');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->deleteViolationTypes('violation_types_20190517.csv');
        $this->deleteViolationAdminRights('admin_rights_violation_20190517.csv');
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
    protected function loadViolationTypes($filename)
    {
        Console::output(Console::ansiFormat("Load data to violation_cancellation", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $data = $this->parseCsv($filename);
        foreach ($data as $cancellation) {
            Console::output(Console::ansiFormat($cancellation[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->insert('violation_type', [
                    'name' => $cancellation[0],
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
    protected function deleteViolationTypes($filename)
    {
        Console::output(Console::ansiFormat("Del data from violation_cancellation", [
            Console::FG_YELLOW, Console::BOLD,
        ]));

        $data = $this->parseCsv($filename);
        foreach ($data as $cancellation) {
            Console::output(Console::ansiFormat($cancellation[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->delete(
                    'violation_type',
                    'name = :name',
                    [
                        'name' => $cancellation[0],
                    ]
                )->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            }
        }
    }

    /**
     * @param $filename
     * @throws Throwable
     */
    protected function loadViolationAdminRights($filename)
    {
        Console::output(Console::ansiFormat("Load data to violation_admin_rights", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $data = $this->parseCsv($filename);
        foreach ($data as $admin_rights) {
            Console::output(Console::ansiFormat($admin_rights[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->insert('violation_admin_rights', [
                    'short_name' => $admin_rights[0],
                    'full_name'  => $admin_rights[1],
                    'description'=> $admin_rights[2],
                ])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                throw $e;
            }
        }
    }

    /**
     *
     */
    protected function deleteViolationAdminRights($filename)
    {
        Console::output(Console::ansiFormat("Del data from violation_admin_rights", [
            Console::FG_YELLOW, Console::BOLD,
        ]));

        $data = $this->parseCsv($filename);
        foreach ($data as $admin_rights) {
            Console::output(Console::ansiFormat($admin_rights[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->delete(
                    'violation_admin_rights',
                    'short_name = :short_name AND full_name = :full_name AND description = :description',
                    [
                        'short_name' => $admin_rights[0],
                        'full_name'  => $admin_rights[1],
                        'description'=> $admin_rights[2],
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
        echo "m190515_120111_load_violations cannot be reverted.\n";

        return false;
    }
    */
}
