<?php


use app\commands\migrate\Migration;

/**
 * Class m240405_084800_insert_hlorid_params_to_service_0310_Hloridi
 */
class m240405_084800_insert_hlorid_params_to_service_0310_Hloridi extends Migration
{
    public function up()
    {
        $id_service = (new \yii\db\Query())
            ->select('id')
            ->from('public.gov_services')
            ->where(['cod' => '0310', 'id_pricelist' => 14])
            ->scalar();

        $id_params = (new \yii\db\Query())
            ->select('id')
            ->from('public.params')
            ->where(['tech_name' => ['P83_Chloridemmdesc', 'P82_Chloridemmvalue']])
            ->column();

        foreach ($id_params as $id_param) {
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
        }
    }


    public function down()
    {
        $id_service = (new \yii\db\Query())
            ->select('id')
            ->from('public.gov_services')
            ->where(['cod' => '0310', 'id_pricelist' => 14])
            ->scalar();

        $this->delete('public.gov_services_params', ['id_service' => $id_service]);
    }

}
