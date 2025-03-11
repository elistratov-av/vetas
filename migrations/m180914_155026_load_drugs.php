<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use \yii\helpers\Console;

/**
 * Class m180802_155026_load_drugs
 */
class m180914_155026_load_drugs extends Migration
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
     * @throws Exception
     * @throws Throwable
     */
    protected function loadDrugOrgs()
    {
        Console::output(Console::ansiFormat("Загрузка организаций производителей", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $data = $this->parseCsv('01_drug_orgs.csv');
        foreach ($data as $org) {
            Console::output(Console::ansiFormat($org[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->insert('drug_orgs', [
                    'name' => $org[0],
                ])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                throw $e;
            }
        }
    }

    protected function deleteDrugOrgs()
    {
        Console::output(Console::ansiFormat("Удаление организаций производителей", [
            Console::FG_YELLOW, Console::BOLD,
        ]));

        $data = $this->parseCsv('01_drug_orgs.csv');
        foreach ($data as $org) {
            Console::output(Console::ansiFormat($org[0], [Console::FG_YELLOW]));
            try {
                $this->db->createCommand()->delete('drug_orgs', 'name = :name', [
                    'name' => $org[0],
                ])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            }
        }
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    protected function loadDrugs()
    {
        Console::output(Console::ansiFormat("Загрузка препаратов", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $data = $this->parseCsv('01_drugs.csv');

        $tmcTypes = $this->findTmcTypes();

        foreach ($data as $drugs) {
            $name = $drugs[2];
            $tmcClass = $drugs[0];
            $tmcTypeName = $drugs[1];

            Console::output(Console::ansiFormat($tmcClass . ' - ' . $name, [Console::FG_YELLOW]));

            $tmcType = ArrayHelper::getValue($tmcTypes[$tmcClass], $tmcTypeName);

            $tableName = $tmcClass . 's';

            try {
                if (empty($tmcType)) {
                    Console::output(Console::ansiFormat(' - creating tmc_type ' . $tmcTypeName, [Console::FG_GREY]));
                    $tmcType = [
                        'name' => $tmcTypeName,
                        'tmc_class' => $tmcClass,
                    ];
                    try {
                        $this->db->createCommand()
                            ->insert('tmc_types', $tmcType)
                            ->execute();
                        $tmcType['id'] = $this->db->getLastInsertID('tmc_types_id_seq');
                        $tmcTypes[$tmcClass][$tmcTypeName] = $tmcType;
                    } catch (\Throwable $e) {
                        Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                        throw $e;
                    }
                }

                $this->db->createCommand()->insert($tableName, [
                    'name' => $name,
                    'id_tmc_type' => $tmcType['id'],
                    'form' => '',   // фикс для not null у vaccines
                    'id_registered' => 1,   // фикс для not null и FK
                    'id_produced' => 1,   // фикс для not null и FK
                    'id_dealer' => 1,   // фикс для not null null и FK
                ])->execute();

                $drug_id = $this->db->getLastInsertID('tmc_id_seq');

                $this->db->createCommand()->insert('drugs_tmp', [
                    'id' => $drug_id,
                    'name' => $name,
                    'tmc_class' => $tmcClass,
                ])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                throw $e;
            }
        }
    }

    /**
     * @param $drugName
     * @return array|bool
     */
    private static function findDrugByName($drugName)
    {
        $drug = (new Query())
            ->select(['id', 'tmc_class'])
            ->from('drugs_tmp')
            ->where(['name' => $drugName])
            ->one();

        return $drug;
    }

    protected function linkDrugsAndOrgs()
    {
        Console::output(Console::ansiFormat("Связываем препараты и организации", [Console::FG_YELLOW, Console::BOLD]));

        $data = $this->parseCsv('01_drugs_to_org.csv');

        foreach ($data as $row) {
            $drugName = $row[0];
            $drug = self::findDrugByName($drugName);
            if (empty($drug)) {
                Console::output(Console::ansiFormat('not found - ' . $drugName, [Console::FG_RED]));
                continue;
            }
            if (empty($drug['tmc_class'])) {
                Console::output(Console::ansiFormat('tmc_class is not set for ' . $drugName, [Console::FG_RED]));
                continue;
            }

            $tableName = $drug['tmc_class'] . 's';

            $command = $this->db->createCommand(
                'update ' . $tableName . ' set 
                        id_registered = (select id from drug_orgs where name = :name_registered),
                        id_produced = (select id from drug_orgs where name = :name_manufactured),
                        id_dealer = (select id from drug_orgs where name = :name_representation)
                    where id = :drug_id'
            );
            $command->bindValues([
                'name_manufactured' => $row[1],
                'name_registered' => $row[2],
                'name_representation' => $row[3],
                'drug_id' => $drug['id'],
            ])->execute();
        }
    }

    /**
     * @param $field
     * @param $file
     * @throws Exception
     * @throws Throwable
     */
    public function updateDrugsField($field, $file)
    {
        Console::output(Console::ansiFormat("Загрузка {$field}", [Console::FG_YELLOW, Console::BOLD]));

        $data = $this->parseCsv($file);

        foreach ($data as $row) {
            $drugName = $row[0];
            $drug = self::findDrugByName($drugName);
            if (empty($drug)) {
                Console::output(Console::ansiFormat('not found - ' . $drugName, [Console::FG_RED]));
                continue;
            }
            if (empty($drug['tmc_class'])) {
                Console::output(Console::ansiFormat('tmc_class is not set for ' . $drugName, [Console::FG_RED]));
                continue;
            }

            $tableName = $drug['tmc_class'] . 's';

            $command = $this->db->createCommand(
                "update {$tableName} set {$field} = :{$field} where id = :drug_id"
            );
            $command->bindValues([
                $field => $row[1],
                'drug_id' => $drug['id'],
            ])->execute();
        }

    }

    /**
     * Пока не используем - позже сделаем отдельную миграцию на описания
     * TODO - нужно будет поправить поля!
     * @throws \Throwable
     */
    protected function loadDescriptions()
    {
        Console::output(Console::ansiFormat("Загрузка описаний", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $tmp = $this->parseCsv('01_drugs_descriptions.csv');
        $data = [];
        $currentType = '';
        foreach ($tmp as $row) {
            if (!isset($data[$row[0]])) {
                $data[$row[0]] = [];
            }

            if (!empty($row[1])) {
                $currentType = $row[1];
            }

            if (!isset($data[$row[0]][$currentType])) {
                $data[$row[0]][$currentType] = '';
            }

            $data[$row[0]][$currentType] .= $row[2];
        }

        $sql = <<<SQL
insert into descriptions (entity_id, description, id_description_type)
values (
  (select id from drugs_tmp where csv_id = :csv_id), 
  :description,
  (select id from description_types where name = :description_name and entity_type = 'drug') 
) 
SQL;

        $this->db->transaction(function () use ($data, $sql) {
            $descriptionsCommand = $this->db->createCommand($sql);
            $typesCommand = $this->db->createCommand("
                INSERT INTO description_types (name, entity_type) VALUES (:name, :entity_type)
                ON CONFLICT (name) DO NOTHING
            ");
            foreach ($data as $csv_id => $descriptions) {
                foreach ($descriptions as $name => $description) {
                    try {
                        $typesCommand->bindValues([
                            'name' => $name,
                            'entity_type' => 'drug'
                        ])->execute();

                        $descriptionsCommand->bindValues([
                            'csv_id' => $csv_id,
                            'description' => $description,
                            'description_name' => $name
                        ])->execute();
                    } catch (Exception $e) {
                        print_r($e->getMessage());
                    }
                }

            }
        });
    }

    /**
     * @return array
     */
    private function findTmcTypes()
    {
        $rows = (new Query())
            ->select(['id', 'name', 'tmc_class'])
            ->from('tmc_types')
            ->where(['in', 'tmc_class', ['drug', 'vaccine']])
            ->all();

        if (empty($rows)) {
            return [
                'drug' => [],
                'vaccine' => [],
            ];
        }

        $data = ArrayHelper::index($rows, 'name', 'tmc_class');

        foreach (['drug', 'vaccine'] as $tmcClass) {
            if (!array_key_exists($tmcClass, $data)) {
                $data[$tmcClass] = [];
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTmpTable();
        $this->createTmpTable();
        $this->resetSequences();

        $this->loadDrugOrgs();
        $this->loadDrugs();
        $this->linkDrugsAndOrgs();
        $this->updateDrugsField('basis', '01_drugs_basis.csv');
        $this->updateDrugsField('excipients', '01_drugs_excipients.csv');
        $this->updateDrugsField('form', '01_drugs_form.csv');
        $this->updateDrugsField('form_description', '01_drugs_form_description.csv');
        $this->updateDrugsField('packaging', '01_drugs_packaging.csv');

        // reg_doc пока не трогаем
        // Юлия, 11:10, 04/09/2018
        // по поводу reg_doc - как договорились. пока не заливаем. потом необходимо будет сделать справочник типов
        // регистрационных документов и добавить вакцинам/препаратам связь с типом и атрибут-значение документа

        // $this->updateDrugsField('reg_doc', '01_drugs_reg_doc.csv');

        // с описаниями у аналитиков пока не все Ок, пока не загружаем
        // $this->loadDescriptions();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("delete from drugs where id in (select id from drugs_tmp)");
        $this->execute("delete from vaccines where id in (select id from drugs_tmp)");
        $this->deleteDrugOrgs();

        $this->dropTmpTable();
    }

    private function createTmpTable()
    {
        $this->createTable('drugs_tmp', [
            'id' => $this->integer(),
            'name' => $this->string()->unique(),
            'tmc_class' => $this->string(),
        ]);
        $this->addPrimaryKey('drugs_tmp_pkey', 'drugs_tmp', ['id', 'name']);
    }

    private function dropTmpTable()
    {
        $this->execute('drop table if exists drugs_tmp');
    }

    private function resetSequences()
    {
        $tables = [
            'drug_orgs',
            'tmc_types',
            'tmc',
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            $this->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
    }
}
