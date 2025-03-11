<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use app\models\db\ReportsParams;
use yii\db\Query;

/**
 * Class m200130_064431_2594_fix_report_params
 */
class m200130_064431_2594_fix_report_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // В отчете "Общий анализ мочи" нет возможности ввести параметр "Лейкоциты" (в шаблоне он есть и заполняется),
        // зато есть "Лейкоциты - примечание", может нужно оставить параметр 'P34_Leucocytvalue', а примечание убрать?
        // P34_Leucocytvalue
        // P35_Leucocytdesc

        $id_report = 5;

        $id_service = (new Query())
            ->select('id')
            ->from(GovServices::tableName())
            ->where(['=', 'name', 'Общий анализ мочи'])
            ->scalar();

        if (!empty($id_service)) {
            $id_param = (new Query())
                ->select('id')
                ->from(Params::tableName())
                ->where(['tech_name' => 'P35_Leucocytdesc'])
                ->scalar();
            if (!empty($id_param)) {
                $this->delete(
                    GovServicesParams::tableName(),
                    [
                        'and',
                        ['=', 'id_param', $id_param],
                        ['=', 'id_service', $id_service],
                    ]);
                $this->delete(
                    ReportsParams::tableName(),
                    [
                        'and',
                        ['=', 'id_param', $id_param],
                        ['id_report' => $id_report],
                    ]);
            }

            $id_param = (new Query())
                ->select('id')
                ->from(Params::tableName())
                ->where(['tech_name' => 'P34_Leucocytvalue'])
                ->scalar();

            if (!empty($id_param)) {
                $gsp = new GovServicesParams([
                    'id_param' => $id_param,
                    'id_service' => $id_service,
                    'req_in' => false,
                    'req_out' => false,
                    'flag_in' => false,
                    'flag_out' => true,
                    'sort_by' => 23
                ]);
                $gsp->save();

                $rp = new ReportsParams([
                    'id_param' => $id_param,
                    'id_report' => $id_report,
                ]);
                $rp->save();
            }
        }

        // В отчете "Общий клинический анализ крови" остались связи данных параметров с услугами,
        // из-за этого параметры отображаются на фронте, но в самом отчете их нет

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

        $service_ids = (new Query())
            ->select('id')
            ->from(GovServices::tableName())
            ->where([
                'in',
                'name',
                [
                    'Общий клинический анализ крови - выведение лейкоцитарной формулы',
                    'Общий клинический анализ крови - определение СОЭ',
                    'Общий клинический анализ крови - подсчет форменных элементов крови (эритроцитов, лейкоцитов) с определением гемоглобина'
                ]
            ])
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

        // В отчетах УЗИ "печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта",
        // "репродуктивная система самки" можно сдвинуть параметры "Заключение" и "Примечание" в конец

        $this->update(
            GovServicesParams::tableName(),
            ['sort_by' => 998],
            [
                'in',
                'id_param',
                (new Query())
                    ->select('id')
                    ->from(Params::tableName())
                    ->where(['in', 'tech_name', ['P48_Serviceresult', 'P32_Serviceresult']])
            ]
        );
        $this->update(
            GovServicesParams::tableName(),
            ['sort_by' => 999],
            [
                '=',
                'id_param',
                (new Query())
                    ->select('id')
                    ->from(Params::tableName())
                    ->where(['=', 'tech_name', 'P13_Serviceresultdesc'])
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200130_064431_2594_fix_report_params cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200130_064431_2594_fix_report_params cannot be reverted.\n";

        return false;
    }
    */
}
