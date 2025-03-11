<?php

use app\commands\migrate\Migration;
use app\models\db\Dictionaries;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\GovServicesReports;
use app\models\db\Params;
use app\models\db\Reports;

/**
 * Class m240730_135933_update_dictionaries_table
 */
class m240730_135933_1361_dev extends Migration
{

    const SERVICE_ID = 2070;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $config = [
                [
                    "report" => "Ультразвуковое исследование щитовидной железы",
                    "req_in" => [
                        "value" => "щитовидная, паращитовидная железа",
                        "tech_name" => "P0_Organsystem"
                    ]
                ]
            ];
            $dictionary = new Dictionaries();
            $dictionary->name = 'щитовидная, паращитовидная железа';
            $dictionary->type = 'ultrasoundorgansystem';
            if (!Dictionaries::findOne(['name' => $dictionary->name]) && !$dictionary->save()) {
                $transaction->rollBack();
                return false;
            }
            $data = [
                [
                    'name' => 'Правая доля щитовидной железы: размер',
                    'tech_name' => 'P1_Pravaya_dolya_shchitovidnoy_zhelezy_razmer',
                    'datatype' => 'numeric',
                    'datatype_details' => '4,2',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Правая доля щитовидной железы: контуры',
                    'tech_name' => 'P2_Pravaya_dolya_shchitovidnoy_zhelezy_kontury',
                    'datatype' => 'text',
                    'datatype_details' => '100',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Правая доля щитовидной железы: эхогенность',
                    'tech_name' => 'P3_Pravaya_dolya_shchitovidnoy_zhelezy_echogennost',
                    'datatype' => 'text',
                    'datatype_details' => '100',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Правая доля щитовидной железы: новообразования',
                    'tech_name' => 'P4_Pravaya_dolya_shchitovidnoy_zhelezy_novoobrazovaniya',
                    'datatype' => 'text',
                    'datatype_details' => '100',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Левая доля щитовидной железы: размер',
                    'tech_name' => 'P5_Levaya_dolya_shchitovidnoy_zhelezy_razmer',
                    'datatype' => 'numeric',
                    'datatype_details' => '4,2',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Левая доля щитовидной железы: контуры',
                    'tech_name' => 'P6_Levaya_dolya_shchitovidnoy_zhelezy_kontury',
                    'datatype' => 'text',
                    'datatype_details' => '100',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Левая доля щитовидной железы: эхогенность',
                    'tech_name' => 'P7_Levaya_dolya_shchitovidnoy_zhelezy_echogennost',
                    'datatype' => 'text',
                    'datatype_details' => '100',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Левая доля щитовидной железы: новообразования',
                    'tech_name' => 'P8_Levaya_dolya_shchitovidnoy_zhelezy_novoobrazovaniya',
                    'datatype' => 'text',
                    'datatype_details' => '100',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Паращитовидная железа правая доля: размер (мм)',
                    'tech_name' => 'P9_Parashchitovidnaya_zheleza_pravaya_dolya_razmer',
                    'datatype' => 'numeric',
                    'datatype_details' => '4,2',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Паращитовидная железа левая доля: размер (мм)',
                    'tech_name' => 'P10_Parashchitovidnaya_zheleza_levaya_dolya_razmer',
                    'datatype' => 'numeric',
                    'datatype_details' => '4,2',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Паращитовидная железа: визуализация',
                    'tech_name' => 'P11_Parashchitovidnaya_zheleza_vizualizatsiya',
                    'datatype' => 'text',
                    'datatype_details' => '100',
                    'visit_flag' => false,
                    'config' => $config,
                ],
                [
                    'name' => 'Заключение',
                    'tech_name' => 'P112_Serviceresult',
                    'datatype' => 'text',
                    'datatype_details' => '3000',
                    'visit_flag' => false,
                    'config' => $config,
                ]
            ];
            $counter = 0;
            foreach ($data as $dataParam) {
                if (Params::findOne(['tech_name' => $dataParam['tech_name']])) continue;
                $param = new Params();
                $param->name = $dataParam['name'];
                $param->tech_name = $dataParam['tech_name'];
                $param->datatype = $dataParam['datatype'];
                $param->datatype_details = $dataParam['datatype_details'];
                $param->visit_flag = $dataParam['visit_flag'];
                $param->config = $dataParam['config'];
                if (!$param->save()) {
                    $transaction->rollBack();
                    return false;
                }
                ++$counter;
                $govServiceParam = new GovServicesParams();
                $govServiceParam->id_param = $param->id;
                $govServiceParam->id_service = static::SERVICE_ID;
                $govServiceParam->flag_out = true;
                $govServiceParam->req_in = false;
                $govServiceParam->req_out = false;
                $govServiceParam->sort_by = $counter;
                if (!$govServiceParam->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }
            $report = new Reports();
            $report->name = 'Ультразвуковое исследование щитовидной железы';
            $report->report_type = 'R';
            if (!$report->save()) {
                $transaction->rollBack();
                return false;
            }
            $govServiceReport = new GovServicesReports();
            $govServiceReport->id_report = $report->id;
            $govServiceReport->id_service = static::SERVICE_ID;
            if (!$govServiceReport->save()) {
                $transaction->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $tech_names = [
                'P1_Pravaya_dolya_shchitovidnoy_zhelezy_razmer',
                'P2_Pravaya_dolya_shchitovidnoy_zhelezy_kontury',
                'P3_Pravaya_dolya_shchitovidnoy_zhelezy_echogennost',
                'P4_Pravaya_dolya_shchitovidnoy_zhelezy_novoobrazovaniya',
                'P5_Levaya_dolya_shchitovidnoy_zhelezy_razmer',
                'P6_Levaya_dolya_shchitovidnoy_zhelezy_kontury',
                'P7_Levaya_dolya_shchitovidnoy_zhelezy_echogennost',
                'P8_Levaya_dolya_shchitovidnoy_zhelezy_novoobrazovaniya',
                'P9_Parashchitovidnaya_zheleza_pravaya_dolya_razmer',
                'P10_Parashchitovidnaya_zheleza_levaya_dolya_razmer',
                'P11_Parashchitovidnaya_zheleza_vizualizatsiya',
                'P112_Serviceresult'
            ];
            foreach ($tech_names as $tech_name) {
                $param = Params::findOne(['tech_name' => $tech_name]);
                if ($param) {
                    $govServiceParam = GovServicesParams::findOne(['id_param' => $param->id]);
                    if ($govServiceParam) $govServiceParam->delete();
                    $param->delete();
                }
            }
            $dictionary = Dictionaries::findOne(['name' => 'щитовидная, паращитовидная железа']);
            if ($dictionary) $dictionary->delete();
            $report = Reports::findOne(['name' => 'Ультразвуковое исследование щитовидной железы']);
            if ($report) {
                $govServiceReport = GovServicesReports::findOne(['id_report' => $report->id]);
                if ($govServiceReport) $govServiceParam->delete();
                $report->delete();
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }
}
