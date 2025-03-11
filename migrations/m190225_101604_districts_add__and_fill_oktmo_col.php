<?php

use app\commands\migrate\Migration;
use yii\db\Connection;
use yii\helpers\Console;

/**
 * Class m190225_101604_districts_add__and_fill_oktmo_col
 */
class m190225_101604_districts_add__and_fill_oktmo_col extends Migration
{
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
     * {@inheritdoc}
     * @throws Throwable
     */
    public function safeUp()
    {
        $this->addColumn('districts', 'oktmo', $this->string());

        Console::output(Console::ansiFormat("Загрузка ОКТМО кодов", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $data = $this->parseCsv('OKTMO_Mosscow.csv');
        foreach ($data as $oktmo_code) {
            Console::output(Console::ansiFormat("запись $oktmo_code[0] - $oktmo_code[1]", [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->update('public.districts', [
                    'oktmo' => $oktmo_code[0],
                ],[
                    'name' => $oktmo_code[1]
                ])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                throw $e;
            }
        }

        $this->addColumn('fias_addresses', 'id_area', $this->integer());
        $this->addColumn('fias_addresses', 'id_district', $this->integer());
        $this->addColumn('fias_addresses', 'oktmo', $this->string());

        $proc = <<<SQL
create function fias_addresses_id_area_id_district_func()
  returns trigger
language plpgsql
as $$
DECLARE
    address record;
BEGIN
    IF NEW.oktmo IS NOT NULL THEN
      IF EXISTS(select 1 from districts where districts.oktmo = NEW.oktmo) THEN 
        SELECT id_area, id as id_district INTO STRICT address FROM districts WHERE districts.oktmo = NEW.oktmo;
        NEW.id_area := address.id_area;
        NEW.id_district := address.id_district;
        RETURN NEW;
      END IF; 
    END IF;
    
    NEW.id_area := NULL;
    NEW.id_district := NULL;
    
    
    RETURN NEW;
END;
$$;
SQL;
        $this->execute($proc);

        $trg = <<<SQL
create trigger fias_address_id_area_id_district_trg
  before insert or update
  on fias_addresses
  for each row
execute procedure fias_addresses_id_area_id_district_func();
SQL;
        $this->execute($trg);

        /** @var Connection $db */
        $db = \Yii::$app->dbFias;

        $addrs_with_street = (new \yii\db\Query())
            ->from('public.fias_addresses')
            ->andWhere(['not', ['streetguid' => null]])
            ->all();

        $query = new \yii\db\Query();
        $query2 = new \yii\db\Query();

        foreach ($addrs_with_street as $addr){
            $oktmo_addr = $query->from('addrob')
                ->where([
                    'aoguid' => $addr['streetguid'],
                    'livestatus' => 1
                ])
                ->select('oktmo')
                ->scalar($db);

            if(!$oktmo_addr && isset($addr['houseguid'])) {
                $oktmo_addr = $query2->from('house')
                    ->where([
                        'houseguid' => $addr['houseguid'],
                        'enddate' => '2079-06-06 00:00:00',
                    ])
                    ->select('oktmo')
                    ->scalar($db);
            }

            if(!$oktmo_addr)
                continue;

            try {
                $this->db->createCommand('update fias_addresses 
                    set oktmo  = :oktmo
                    where id = :id_addr',[':oktmo' => $oktmo_addr, ':id_addr' => $addr['id']])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                throw $e;
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190225_101604_districts_add__and_fill_oktmo_col cannot be reverted.\n";

        return false;
    }
}
