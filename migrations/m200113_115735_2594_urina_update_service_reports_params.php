<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use app\models\db\ReportsParams;
use yii\db\Query;

/**
 * Class m200113_115735_2594_urina_update_service_reports_params
 */
class m200113_115735_2594_urina_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $id_report = 5;

        $removed = [
            'P15_Colorurinedesc',
            'P17_Transparencydesc',
            'P19_Aciditydesc',
            'P21_Proteindesc',
            'P23_Glukozadesc',
            'P25_Ketonboddesc',
            'P27_Relativedensitydesc',
            'P29_Bilirubindesc',
            'P31_Hemegldesc',
            'P33_Erythrocytdesc',
            'P34_Leucocytvalue',
            'P37_Ploskiydesc',
            'P39_Perehoddesc',
            'P41_Pochechndesc',
            'P43_Hyalinedesc',
            'P45_Granulardesc',
            'P47_Waxdesc',
            'P49_Lekocitdesc',
            'P51_Eritrocitdesc',
            'P53_Epiteldesc',
            'P55_Cilinddesc',
            'P57_Bacteriadesc',
            'P59_Saltdesc',
        ];

        $params_ids = (new Query())
            ->select('id')
            ->from(Params::tableName())
            ->where(['in', 'tech_name', $removed])
            ->column();

        $id_service = (new Query())
            ->select('id')
            ->from(GovServices::tableName())
            ->where(['=', 'name', 'Общий анализ мочи'])
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
            'P63_Nitritvalue' => 'Нитриты',
            'P24_Urobilinogenvalue' => 'Уробилиноген',
            'P54_Cilindervalue' => 'Цилиндры',
            'P56_Slizvalue' => 'Слизь',
            'P56_Othervalue' => 'Прочее',
        ];

        foreach ($created as $tech_name => $name) {
            $param = new Params([
                'tech_name' => $tech_name,
                'name' => ($name . ' - результат исследования'),
                'datatype' => 'text',
                'datatype_details' => '50',
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
        echo "m200113_115735_2594_urina_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
