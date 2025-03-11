<?php

use app\commands\migrate\Migration;

/**
 * Class m240618_145550_insert_report_to_service_0310_Hloridi
 */
class m240618_145550_insert_report_to_service_0310_Hloridi extends Migration
{
    public function up()
    {
        // получаем id услуги Показатели биохимического анализа крови - определение хлоридов
        $id_service = (new \yii\db\Query())
            ->select('id')
            ->from('public.gov_services')
            ->where(['cod' => '0310', 'id_pricelist' => 14, 'deleted' => false])
            ->scalar();
        //

        // получаем id отчета Показатели биохимического анализа крови - определение хлоридов
        $id_existingReport = (new \yii\db\Query())
            ->select('id')
            ->from('public.reports')
            ->where([
                'name' => 'Показатели биохимического анализа крови - определение хлоридов',
                'report_type' => 'R',
                'sending' => true,
            ])
            ->scalar();
        //

        if ($id_existingReport) {
            $this->delete('public.gov_services_reports', ['id_service' => $id_service]);
        }


        $this->insert('public.gov_services_reports', [
            'id_report' => $id_existingReport,
            'id_service' => $id_service,
        ]);

    }


    public function down()
    {
        $id_service = (new \yii\db\Query())
            ->select('id')
            ->from('public.gov_services')
            ->where(['cod' => '0310', 'id_pricelist' => 14])
            ->scalar();

        $this->delete('public.gov_services_params', ['id_service' => $id_service]);

        $this->delete('public.gov_services_reports', ['id_service' => $id_service]);
    }
}
