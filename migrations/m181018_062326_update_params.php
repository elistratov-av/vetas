<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m181018_062326_update_params
 */
class m181018_062326_update_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->resetSequences();

        $sql = <<<SQL
update params set datatype_details = lower(datatype_details) where datatype = 'dict';
SQL;
        $this->execute($sql);


        // https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=96313113&focusedCommentId=96318749#comment-96318749
        // Руменко Леонид Андреевич

        // Скорректированы наименования (param.name) атрибутов:
        // P40_Trikuspklapnregurgitatsiya - Трикуспидальный клапан: Регургитация~[РЛА7]~
        // P46_Klapnlegartregurgitatsiya - Клапан легочной артерии: Регургитация~[РЛА8]~
        // P37_Mitrklapnregurgitatsiya - Митральный клапан: Регургитация~[РЛА6]~

        $tableName = 'params';

        $params = [
            'P40_Trikuspklapnregurgitatsiya' => 'Трикуспидальный клапан: Регургитация',
            'P46_Klapnlegartregurgitatsiya' => 'Клапан легочной артерии: Регургитация',
            'P37_Mitrklapnregurgitatsiya' => 'Митральный клапан: Регургитация',
        ];

        foreach ($params as $tech_name => $name) {
            $this->update($tableName, ['name' => $name], ['tech_name' => $tech_name]);
        }

        // Изменен тип параметра с "dict" на "numeric(4,2)" для следующих параметров:
        // P36_Ploskiyvalue
        // P38_Perehodvalue
        // P40_Pochechnvalue
        // P32_Erythrocytvalue
        // P56_Bacteriavalue

        $params = [
            'P36_Ploskiyvalue',
            'P38_Perehodvalue',
            'P40_Pochechnvalue',
            'P32_Erythrocytvalue',
            'P56_Bacteriavalue',
        ];

        foreach ($params as $tech_name) {
            $this->update($tableName, ['datatype' => 'numeric', 'datatype_details' => '4,2'], ['tech_name' => $tech_name]);
        }

        // https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=96313113&focusedCommentId=100402576#comment-100402576
        // Руменко Леонид Андреевич

        // В Param добавлены записи:
        // P0_RgraphyFile
        // P0_UltrasoundFile
        // P0_ScreenEchoFile
        // P0_UltrasoundScreenFile
        // P0_CTScanFile
        // P0_EchoFile

        $csv = <<<CSV
P0_RgraphyFile;Вложение;file;Rgraphy;false
P0_UltrasoundFile;Вложение;file;Ultrasound;false
P0_ScreenEchoFile;Вложение;file;ScreenEcho;false
P0_UltrasoundScreenFile;Вложение;file;UltrasoundScreen;false
P0_CTScanFile;Вложение;file;CTScan;false
P0_EchoFile;Вложение;file;Echo;false
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $tech_name = $item[0];
            $name = empty($item[1]) ? $tech_name : $item[1];    // фикс для пустых name
            $datatype = $item[2];
            $datatype_details = $item[3];
            $visit_flag = ($item[4] == '') ? false : ($item[4] === 'true' ? true : false);
            $columns = compact('name', 'tech_name', 'datatype', 'datatype_details', 'visit_flag');
            $record = $this->findParamByTechName($tech_name);
            if (empty($record)) {
                Console::output(Console::ansiFormat('Creating param [' . $tech_name  . ']', [Console::FG_GREEN]));
                \Yii::$app->db->createCommand()
                    ->insert($tableName, $columns)
                    ->execute();
            } else {
                foreach ($columns as $column => $value) {
                    if ($record[$column] !== $value) {
                        // надо обновить param
                        // остальные поля можно не проверять
                        Console::output(Console::ansiFormat('Updating param [' . $tech_name  . ']', [Console::FG_YELLOW]));
                        \Yii::$app->db->createCommand()
                            ->update($tableName, $columns, ['id' => $record['id']])
                            ->execute();
                        break;
                    }
                }
            }
        }

        // В ServiceParam добавлены записи:
        // R-графия P0_RgraphyFile
        // Ультразвуковое исследование P0_UltrasoundFile
        // Повторное ультразвуковое исследование P0_UltrasoundFile
        // Скрининговое ЭХО-кардиографическое исследование P0_ScreenEchoFile
        // Ультразвуковой скрининг органов брюшной полости P0_UltrasoundScreenFile
        // Компьютерная томография без введения контрастного вещества - голова, отделы конечностей (сустав, регион конечности) P0_CTScanFile
        // Компьютерная томография без введения контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы P0_CTScanFile
        // Компьютерная томография с введением контрастного вещества - голова, отделы конечностей (сустав, регион конечности) P0_CTScanFile
        // Компьютерная томография с введением контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы P0_CTScanFile
        // Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - голова, отделы конечностей (сустав, регион конечности) P0_CTScanFile
        // Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - шейный, грудной, поясничный, крестцово-тазовый отделы P0_CTScanFile
        // ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов P0_EchoFile

        $tableName = 'gov_services_params';

        $csv = <<<CSV
R-графия;P0_RgraphyFile;;false;false;999
Ультразвуковое исследование;P0_UltrasoundFile;;false;false;999
Повторное ультразвуковое исследование;P0_UltrasoundFile;;false;false;999
Скрининговое ЭХО-кардиографическое исследование;P0_ScreenEchoFile;;false;false;999
Ультразвуковой скрининг органов брюшной полости;P0_UltrasoundScreenFile;;false;false;999
Компьютерная томография без введения контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_CTScanFile;;false;false;999
Компьютерная томография без введения контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_CTScanFile;;false;false;999
Компьютерная томография с введением контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_CTScanFile;;false;false;999
Компьютерная томография с введением контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_CTScanFile;;false;false;999
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - голова, отделы конечностей (сустав, регион конечности);P0_CTScanFile;;false;false;999
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_CTScanFile;;false;false;999
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P0_EchoFile;;false;false;999
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $service_name = $item[0];
            $tech_name = $item[1];
            $req_in = ($item[3] == '') ? false : ($item[3] === 'true' ? true : false);
            $req_out = ($item[4] == '') ? false : ($item[4] === 'true' ? true : false);
            $sort_by = empty($item[5]) ? 0 : (int)$item[5];

            $param = $this->findParamByTechName($tech_name);
            if (empty($param)) {
                Console::output(Console::ansiFormat('Param not found [' . $tech_name  . ']', [Console::FG_RED]));
                continue;
            }

            $gov_service = $this->findRecord('gov_services', ['name' => $service_name]);
            if (empty($gov_service)) {
                Console::output(Console::ansiFormat('Service not found for param [' . $tech_name  . '] - [' . $service_name . ']', [Console::FG_RED]));
                continue;
            }

            $id_service = $gov_service['id'];
            $id_param = $param['id'];

            $columns = compact('id_param', 'id_service', 'req_in', 'req_out', 'sort_by');

            \Yii::$app->db->createCommand()
                ->insert($tableName, $columns)
                ->execute();
        }

        // В ReportParam добавлены записи:
        // УЗИ мочевыдел P0_UltrasoundFile
        // УЗИ печ. и т.д P0_UltrasoundFile
        // УЗИ репр. Самки P0_UltrasoundFile
        // УЗИ репр. Самца P0_UltrasoundFile
        // УЗИ глаза P0_UltrasoundFile

        $tableName = 'reports_params';

        $csv = <<<CSV
Бланк регистрации и вакцинации животных;P15_Vacexpirationdate
Ультразвуковое исследование мочевыделительной системы;P0_UltrasoundFile
Ультразвуковое исследование глаза;P0_UltrasoundFile
Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта;P0_UltrasoundFile
Ультразвуковое исследование репродуктивной системы самки;P0_UltrasoundFile
Ультразвуковое исследование репродуктивной системы самца;P0_UltrasoundFile
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $name = $item[0];
            $tech_name = $item[1];

            $param = $this->findParamByTechName($tech_name);
            if (empty($param)) {
                Console::output(Console::ansiFormat('Param not found [' . $tech_name  . ']', [Console::FG_RED]));
                continue;
            }

            $report = $this->findRecord('reports', ['name' => $name]);
            if (empty($report)) {
                Console::output(Console::ansiFormat('Report not found [' . $name  . ']', [Console::FG_RED]));
                continue;
            }

            $id_param = $param['id'];
            $id_report = $report['id'];

            $columns = compact('id_param', 'id_report');

            \Yii::$app->db->createCommand()
                ->insert($tableName, $columns)
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->resetSequences();
    }

    private function resetSequences()
    {
        $tables = [
            'params',
            'gov_services_params',
            'reports_params',
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
            $arr[] = str_getcsv($row, ';', '');
        }

        if (empty($arr)) {
            throw new \Exception($function . ': Failed to parse CSV');
        }

        return $arr;
    }

    /**
     * @param string $tableName
     * @param array $condition
     * @return array
     */
    private function findRecord($tableName, $condition)
    {
        return (new Query())
            ->from($tableName)
            ->where($condition)
            ->limit(1)
            ->one();
    }

    /**
     * @param $tech_name
     * @return array
     */
    private function findParamByTechName($tech_name)
    {
        return $this->findRecord('params', ['tech_name' => $tech_name]);
    }
}
