<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\GovServicesReports;
use app\models\db\Params;
use app\models\db\Reports;
use app\models\db\ReportsParams;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class m200117_122921_2594_uzi_screening_update_service_reports_params
 */
class m200117_122921_2594_uzi_screening_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        $serviceName = 'Ультразвуковой скрининг органов брюшной полости';

        $service = (new Query())
            ->from(GovServices::tableName())
            ->where(['name' => $serviceName])
            ->one();
        $mainService = (new Query())
            ->from(GovServices::tableName())
            ->where(['name' => 'Ультразвуковое исследование'])
            ->one();

        if (empty($service) || empty($mainService)) {
            Console::output('Service not found');
            return false;
        }

        $id_service = $service['id'];

        $report = new Reports([
            'name' => $serviceName,
            'report_type' => Reports::TYPE_REPORT,
            'grouped' => false,
            'sending' => true,
        ]);

        if (!$report->save(false)) {
            Console::output('Error saving report');
            return false;
        }

        $id_report = $report->id;

        $this->delete(
            GovServicesReports::tableName(),
            [
                'id_service' => $id_service,
            ]
        );

        $this->insert(
            GovServicesReports::tableName(),
            [
                'id_report' => $id_report,
                'id_service' => $id_service,
                'created_at' => $now
            ]
        );

        $reports_ids = [11, 12];

        $reportParams = (new Query)
            ->select('rp.*')
            ->from(ReportsParams::tableName() . ' rp')
            ->leftJoin(Params::tableName() . ' p', 'p.id=rp.id_param')
            ->where(['in', 'rp.id_report', $reports_ids])
            ->andWhere(['!=', 'p.tech_name', 'P0_Organsystem'])
            ->andWhere(['!=', 'p.tech_name', 'P0_Abdomenorgansystem'])
            ->andWhere(['!=', 'p.tech_name', 'P59_Serviceresult'])
            ->orderBy([
                'id_report' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        $reportParamsIds = ArrayHelper::getColumn($reportParams, 'id_param');

        $serviceParams = (new Query)
            ->select('*')
            ->from(GovServicesParams::tableName())
            ->where(['id_service' => $id_service])
            ->orderBy([
                'sort_by' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        $serviceParamsIds = ArrayHelper::getColumn($serviceParams, 'id_param');

        $processed = [];
        foreach ($reportParams as $reportParam) {
            if (in_array($reportParam['id_param'], $processed)) {
                continue;
            }
            $this->insert(
                ReportsParams::tableName(),
                [
                    'id_param' => $reportParam['id_param'],
                    'id_report' => $id_report,
                    'created_at' => $now,
                ]
            );
            $processed[] = $reportParam['id_param'];
            if (!in_array($reportParam['id_param'], $serviceParamsIds)) {
                $this->insert(
                    GovServicesParams::tableName(),
                    [
                        'id_param' => $reportParam['id_param'],
                        'id_service' => $id_service,
                        'req_in' => false,
                        'req_out' => false,
                        'flag_in' => false,
                        'flag_out' => true,
                        'created_at' => $now,
                    ]
                );
            }
        }

        foreach ($serviceParams as $serviceParam) {
            if (!in_array($serviceParam['id_param'], $reportParamsIds)) {
                $this->delete(
                    GovServicesParams::tableName(),
                    [
                        'id' => $serviceParam['id'],
                    ]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200117_122921_2594_uzi_screening_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
