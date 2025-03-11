<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use app\models\db\ReportsParams;
use yii\db\Query;

/**
 * Class m200115_135228_2594_blood_update_service_reports_params
 */
class m200115_135228_2594_blood_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $id_report = 17;

        $removed = [
            'P15_Wbcdesc',
            'P17_Lymdesc',
            'P19_Mondesc',
            'P21_Gradesc',
            'P23_Rbcdesc',
            'P25_Hgbdesc',
            'P27_Hctdesc',
            'P29_Mcvdesc',
            'P31_Mchdesc',
            'P33_Mchcdesc',
            'P35_Rdwdesc',
            'P37_Pltdesc',
            'P39_Mpvdesc',
            'P41_Pctdesc',
            'P43_Pdwdesc',
            'P45_Soedesc',
            'P47_Youngdesc',
            'P49_Palochkoyaderdesc',
            'P51_Segmentdesc',
            'P53_Eosinophilsdesc',
            'P55_Monocitdesc',
            'P57_Bazophildesc',
            'P59_Limphocitdesc',
            'P16_Lymvalue',
            'P18_Monvalue',
            'P20_Gravalue',
            'P38_Mpvvalue',
            'P40_Pctvalue',
            'P42_Pdwvalue',
        ];

        $params_ids = (new Query())
            ->select('id')
            ->from(Params::tableName())
            ->where(['in', 'tech_name', $removed])
            ->column();

        // при обновлении прейскуранта на 2020 год
        // были помечены как удаленные ранее использовавшиеся услуги:
        // 'Общий клинический анализ крови - определение гемоглобина',
        // 'Общий клинический анализ крови - подсчет эритроцитов',
        // 'Общий клинический анализ крови - подсчет лейкоцитов'

        $service_ids = (new Query())
            ->select('id')
            ->from(GovServices::tableName())
            ->where(['in', 'name', ['Общий клинический анализ крови - определение гемоглобина', 'Общий клинический анализ крови - подсчет эритроцитов', 'Общий клинический анализ крови - подсчет лейкоцитов']])
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
                    ['id_report' => $id_report],
                ]);
        }

        // при обновлении прейскуранта на 2020 год
        // была введена услуга:
        // 'Общий клинический анализ крови - подсчет форменных элементов крови (эритроцитов, лейкоцитов) с определением гемоглобина'

        $id_service = (new Query())
            ->select('id')
            ->from(GovServices::tableName())
            ->where(['=', 'name', 'Общий клинический анализ крови - подсчет форменных элементов крови (эритроцитов, лейкоцитов) с определением гемоглобина'])
            ->scalar();

        $created = [
            'P46_Mielvalue' => 'Нейтрофилы - Миелоциты',
            'P58_Morphbloodchangeresult' => 'Морфологические изменения крови',
            'P58_Invasionresult' => 'Исследования на инвазионные болезни',
        ];

        foreach ($created as $tech_name => $name) {
            $param = new Params([
                'tech_name' => $tech_name,
                'name' => ($name . ' - результат исследования'),
                'datatype' => 'text',
                'datatype_details' => ($tech_name == 'P46_Mielvalue' ? '15' : '255'),
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
        echo "m200115_135228_2594_blood_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
