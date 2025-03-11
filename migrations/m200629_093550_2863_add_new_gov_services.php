<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\GovServicesReports;
use app\models\db\Params;
use app\models\db\ReportsParams;
use yii\helpers\Console;

/**
 * Class m200629_093550_2863_add_new_gov_services
 */
class m200629_093550_2863_add_new_gov_services extends Migration
{
    private $services = [
        [
            'Общий клинический анализ крови - определение гематокрита',
            '0395',
            '71.00',
            'P26_Hctvalue',
        ],
        [
            'Общий клинический анализ крови - подсчет тромбоцитов',
            '0396',
            '71.00',
            'P36_Pltvalue',
        ],
        [
            'Общий клинический анализ крови - определение среднего объема эритроцита',
            '0397',
            '71.00',
            'P28_Mcvvalue',
        ],
        [
            'Общий клинический анализ крови - определение среднего содержания гемоглобина в одном эритроците',
            '0398',
            '71.00',
            'P30_Mchvalue',
        ],
        [
            'Общий клинический анализ крови - определение средней концентрации корпускулярного гемоглобина',
            '0399',
            '71.00',
            'P32_Mchcvalue',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->services as $data)
        {
            $govService = new GovServices([
                'name' => $data[0],
                'alternative_name' => $data[0],
                'cod' => $data[1],
                'price' => $data[2],
                'id_pricelist' => 14,
                'id_service_type' => 5,
                'id_specialization' => 11,
                'duration' => 20,
                'cooldown' => 0,
                'id_service_measure' => 10,
                'at_clinic' => true,
                'com_class_journal' => 'additional',
            ]);

            if (!$govService->save()) {
                Console::output('Error saving gov_service "' . $data[0] . '"');
                return false;
            }

            $param = Params::findOne(['tech_name' => $data[3]]);
            if ($param === null) {
                Console::output('Param not found "' . $data[3] . '"');
                return false;
            }

            $gsp = new GovServicesParams([
                'id_param' => $param->id,
                'id_service' => $govService->id,
                'req_in' => false,
                'req_out' => false,
                'flag_in' => false,
                'flag_out' => true,
            ]);

            if (!$gsp->save()) {
                Console::output('Error saving gov_services_params for "' . $data[3] . '" and "' . $data[0] . '"');
                return false;
            }

            $gsr = new GovServicesReports([
                'id_report' => 17,
                'id_service' => $govService->id,
            ]);

            if (!$gsr->save()) {
                Console::output('Error saving gov_services_reports for "' . $data[0] . '"');
                return false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->services as $data) {
            $govService = GovServices::findOne(['name' => $data[0]]);
            if ($govService === null) {
                continue;
            }

            $param = Params::findOne(['tech_name' => $data[3]]);
            if ($param === null) {
                continue;
            }

            Yii::$app->db->createCommand()
                ->delete(GovServicesParams::tableName(), [
                    'id_param' => $param->id,
                    'id_service' => $govService->id,
                ])
                ->execute();

            Yii::$app->db->createCommand()
                ->delete(GovServicesReports::tableName(), [
                    'id_report' => 17,
                    'id_service' => $govService->id,
                ])
                ->execute();

            $govService->delete();
        }
    }
}
