<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m210717_053930_outside_org_data
 */
class m210717_053930_outside_org_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.outside_org','adm_area', $this->string(256));
        $this->addColumn('public.outside_org','district', $this->string(256));
        $this->addColumn('public.outside_org','address', $this->text());
        $this->addColumn('public.outside_org','assigned_registration_number_certificate', $this->string(256));
        $this->addColumn('public.outside_org','validity_certificate', $this->date());
        $this->addColumn('public.outside_org','global_id', $this->integer());

        $data = $this->parseCsv('data-104078-2021-06-23.csv');
        array_shift($data); // Title ignore

        foreach ($data as $row) {
            /*
             * validity_certificate
             */
            $validity_certificate = null;
            if (!empty($row[6])) {
                $date_time = DateTime::createFromFormat('d.m.Y', $row[6]);
                if (!empty($date_time)){
                    $validity_certificate =  $date_time->format('Y-m-d');
                }
            }

            $new_row = [
                'name' => $row[1],
                'adm_area' => $row[2] ?? null,
                'district' => $row[3] ?? null,
                'address' => $row[4] ?? null,
                'assigned_registration_number_certificate' => $row[5] ?? null,
                'validity_certificate' => $validity_certificate,
                'global_id'  => $row[7] ?? null
            ];

            /*
             * Нужны уникальные
             */
            $exist = (new Query())
                ->from('public.outside_org')
                ->where(['name' => $new_row['name']])
                ->exists();

            if ($exist) {
                Console::output(Console::ansiFormat("Организация {$new_row['name']} существует. Пропускаем", [
                    Console::FG_YELLOW
                ]));

                continue;
            }

            /*
             * insert
             */
            $this->insert('public.outside_org', $new_row);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.outside_org','adm_area');
        $this->dropColumn('public.outside_org','district');
        $this->dropColumn('public.outside_org','address');
        $this->dropColumn('public.outside_org','assigned_registration_number_certificate');
        $this->dropColumn('public.outside_org','validity_certificate');
        $this->dropColumn('public.outside_org','global_id');
    }

    protected function parseCsv($file)
    {
        $parseCsv = function ($handle) {
            $data = [];
            while (($row = fgetcsv($handle, 5000, ";")) !== false) {
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

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210717_053930_outside_org_data cannot be reverted.\n";

        return false;
    }
    */
}
