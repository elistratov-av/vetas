<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\GovServicesReports;
use app\models\db\Params;
use app\models\db\ReportsParams;
use yii\db\Query;

/**
 * Class m200109_141219_2594_biochemistry_update_service_reports_params
 */
class m200109_141219_2594_biochemistry_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $removed = [
            'P15_Totalbilirubinmcmdesc',
            'P16_Totalbilirubinmgvalue',
            'P17_Totalbilirubinmgdesc',
            'P18_Conjugatedbilirubinmcmvalue',
            'P19_Conjugatedbilirubinmcmdesc',
            'P20_Conjugatedbilirubinmgvalue',
            'P21_Conjugatedbilirubinmgdesc',
            'P23_Altalanniamvadesc',
            'P25_Astaspartdesc',
            'P27_Mochevinammdesc',
            'P28_Mochevinamgvalue',
            'P29_Mochevinamgdesc',
            'P31_Creatininemcmdesc',
            'P32_Creatininemgvalue',
            'P33_Creatininemgdesc',
            'P35_Shelochfosfatdesc',
            'P37_Amilazadesc',
            'P39_Pancreatinedesc',
            'P41_Glukozamcmdesc',
            'P42_Glukozamgvalue',
            'P43_Glukozamgdesc',
            'P45_Ldglactoddesc',
            'P47_Lgtgammadesc',
            'P49_Kfkcreatinedesc',
            'P51_Holestermmdesc',
            'P52_Holestermgvalue',
            'P53_Holestermgdesc',
            'P55_Triglyceridsmmdesc',
            'P56_Triglyceridsmgvalue',
            'P57_Triglyceridsmgdesc',
            'P59_Caliummmdesc',
            'P60_Caliummecvalue',
            'P61_Caliummecdesc',
            'P63_Natrmmdesc',
            'P64_Natrmecvalue',
            'P65_Natrmecdesc',
            'P67_Phosphormmdesc',
            'P68_Phosphormgvalue',
            'P69_Phosphormgdesc',
            'P71_Calciummmdesc',
            'P72_Calciummcgvalue',
            'P73_Calciummcgdesc',
            'P75_Ironmcmdesc',
            'P76_Ironmcgvalue',
            'P77_Ironmcgdesc',
            'P79_Magnesiummmdesc',
            'P80_Magnesiummecvalue',
            'P81_Magnesiummecdesc',
            'P82_Chloridemmvalue',
            'P83_Chloridemmdesc',
            'P84_Chloridemecvalue',
            'P85_Chloridemecdesc',
            'P86_Acidityvalue',
            'P87_Aciditydesc',
            'P88_Mochekislnmvalue',
            'P89_Mochekislnmdesc',
            'P90_Mochekislmgvalue',
            'P91_Mochekislmgdesc',
            'P93_Lipazadesc',
            'P95_Totalproteingldesc',
            'P96_Totalproteingdlvalue',
            'P97_Totalproteingdldesc',
            'P99_Albumingldesc',
            'P100_Albumingdlvalue',
            'P101_Albumingdldesc',
            'P102_Hemoglobinvalue',
            'P103_Hemoglobindesc',
        ];

        $params_ids = (new Query())
            ->select('id')
            ->from(Params::tableName())
            ->where(['in', 'tech_name', $removed])
            ->column();

        $service_ids = (new Query())
            ->select('id')
            ->from(GovServices::tableName())
            ->where(['like', 'name', 'Биохимические исследования крови%', false])
            ->column();

        if (!empty($params_ids) && !empty($service_ids)) {
            $this->delete(
                GovServicesParams::tableName(),
                [
                    'and',
                    ['in', 'id_param', $params_ids],
                    ['in', 'id_service', $service_ids],
                ]);
        }

        if (!empty($params_ids)) {
            $this->delete(
                ReportsParams::tableName(),
                [
                    'and',
                    ['in', 'id_param', $params_ids],
                    ['id_report' => 2],
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200109_141219_2594_biochemistry_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
