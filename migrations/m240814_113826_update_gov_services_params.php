<?php

use app\commands\migrate\Migration;
use app\models\db\GovServicesParams;
use app\models\db\GovServicesReports;
use app\models\db\Params;
use app\models\db\Reports;

/**
 * Class m240814_113826_update_gov_services_params
 */
class m240814_113826_update_gov_services_params extends Migration
{

    const SERVICE_ID = 2071;
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $data = [
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
            $counter = 0;
            foreach ($data as $dataParam) {
                $param = Params::findOne(['tech_name' => $dataParam]);
                if (!$param) {
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
            $report = Reports::findOne(['name' => 'Ультразвуковое исследование щитовидной железы']);
            if (!$report) {
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
            if (!GovServicesParams::deleteAll(['id_service' => static::SERVICE_ID])) {
                $transaction->rollBack();
                return false;
            }
            if (!GovServicesReports::deleteAll(['id_service' => static::SERVICE_ID])) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }
}
