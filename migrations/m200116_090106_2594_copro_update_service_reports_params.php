<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use app\models\db\ReportsParams;
use yii\db\Query;

/**
 * Class m200116_090106_2594_copro_update_service_reports_params
 */
class m200116_090106_2594_copro_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $id_report = 8;

        $removed = [
            'P15_Coprformdesc',
            'P17_Coprcolordesc',
            'P19_Coprodordesc',
            'P21_Aciditydesc',
            'P23_Stercobilindesc',
            'P25_Bilirubindesc',
            'P27_Blooddesc',
            'P29_Muscledfibersdesc',
            'P31_Contissuefibersdesc',
            'P33_Neutralfatdesc',
            'P35_Fattyacidsdesc',
            'P37_Soapdesc',
            'P39_Starchdesc',
        ];

        $params_ids = (new Query())
            ->select('id')
            ->from(Params::tableName())
            ->where(['in', 'tech_name', $removed])
            ->column();

        $id_service = (new Query())
            ->select('id')
            ->from(GovServices::tableName())
            ->where(['=', 'name', 'Общий анализ кала'])
            ->scalar();

        if (!empty($params_ids) && !empty($id_service)) {
            $this->delete(
                GovServicesParams::tableName(),
                [
                    'and',
                    ['in', 'id_param', $params_ids],
                    ['=', 'id_service', $id_service],
                ]);
        }

        if (!empty($params_ids)) {
            $this->delete(
                ReportsParams::tableName(),
                [
                    'and',
                    ['in', 'id_param', $params_ids],
                    ['id_report' => $id_report],
                ]);
        }

        $created = [
            'P40_Slizvalue' => 'Слизь',
            'P41_Digestibilityvalue' => 'Переваримость корма',
            'P28_Muscledfiberssemivalue' => 'Мышечные волокна полупереваренные',
            'P42_Vegfiberindigestvalue' => 'Растительная клетчатка непереваримая',
            'P43_Vegfiberdigestvalue' => 'Растительная клетчатка переваримая',
            'P38_Starchinnervalue' => 'Крахмал внутриклеточный',
            'P44_Cellelementsvalue' => 'Клеточные элементы',
            'P45_Slizvalue' => 'Слизь',
            'P46_Othervalue' => 'Прочее',
        ];

        foreach ($created as $tech_name => $name) {
            $param = new Params([
                'tech_name' => $tech_name,
                'name' => ($name . ' - результат исследования'),
                'datatype' => 'text',
                'datatype_details' => ($tech_name == 'P46_Othervalue' ? '255' : '50'),
                'visit_flag' => false,
            ]);
            if (!$param->save()) {
                echo 'Error creating param ' . $tech_name . PHP_EOL;
                return false;
            }

            $gsp = new GovServicesParams([
                'id_param' => $param->id,
                'id_service' => $id_service,
                'req_in' => false,
                'req_out' => false,
                'flag_in' => false,
                'flag_out' => true,
            ]);
            $gsp->save();

            $rp = new ReportsParams([
                'id_param' => $param->id,
                'id_report' => $id_report,
            ]);
            $rp->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200116_090106_2594_copro_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
