<?php

use app\commands\migrate\Migration;

/**
 * Class m240424_074258_otchet_usluga_0349_param_1_not_required
 */
class m240424_074258_otchet_usluga_0349_param_1_not_required extends Migration
{
    public function up()
    {
        $id_service = (new \yii\db\Query())
            ->select('id')
            ->from('public.gov_services')
            ->where(['cod' => '0349', 'id_pricelist' => 14])
            ->scalar();

        $id_param = (new \yii\db\Query())
            ->select('id')
            ->from('public.params')
            ->where(['tech_name' => 'P0_Petchpidentificationcode'])
            ->scalar();


        $existingRecord = (new \yii\db\Query())
            ->from('public.gov_services_params')
            ->where(['id_param' => $id_param, 'id_service' => $id_service])
            ->exists();

        if (!$existingRecord) {
            $this->insert('public.gov_services_params', [
                'id_param' => $id_param,
                'id_service' => $id_service,
                'sort_by' => 1,
                'flag_out' => true,
            ]);
        }
        else {
            $this->update('public.gov_services_params', [
                'req_in' => false,
                'req_out' => true,
            ], [
                'id_param' => $id_param,
                'id_service' => $id_service,
            ]);
        }
    }


    public function down()
    {

    }

}
