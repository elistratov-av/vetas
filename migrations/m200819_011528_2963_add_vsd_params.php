<?php

use app\commands\migrate\Migration;
use app\models\db\Dictionaries;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use yii\base\Exception;

/**
 * Class m200819_011528_2963_add_vsd_params
 */
class m200819_011528_2963_add_vsd_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $serviceNames = [
            'Ветеринарное освидетельствование животных для оформления ветеринарных сопроводительных документов, включающая проведение клинического осмотра и изучение ветеринарных документов (паспорта на животное, результатов лабораторных исследований и др.) - без гельминтокопрологического исследования',
            'Ветеринарное освидетельствование животных для оформления ветеринарных сопроводительных документов, включающая проведение клинического осмотра и изучение ветеринарных документов (паспорта на животное, результатов лабораторных исследований и др.) - с гельминтокопрологическим исследованием',
        ];

        $p1 = [
            'name' => 'Тип ВСД',
            'tech_name' => 'P109_Vsdtype',
            'datatype' => 'dict',
            'datatype_details' => 'vsdtypes',
        ];

        $p1_values = [
            'ветеринарное свидетельство',
            'ветеринарный сертификат',
            'ветеринарная справка',
        ];

        $p2 = [
            'name' => 'Номер ВСД',
            'tech_name' => 'P110_Vsdnumber',
            'datatype' => 'text',
            'datatype_details' => '255',
            'config' => [
                'req_out' => [
                    'for' => [
                        'P109_Vsdtype',
                    ],
                ],
            ],
        ];

        foreach ($p1_values as $value) {
            $record = new Dictionaries();
            $record->name = $value;
            $record->type = $p1['datatype_details'];
            if (!$record->save()) {
                throw new Exception('Error saving dictionary value');
            }
        }

        $param1 = new Params($p1);
        if (!$param1->save()) {
            throw new Exception('Error saving param 1');
        }

        $param2 = new Params($p2);
        if (!$param2->save()) {
            throw new Exception('Error saving param 2');
        }

        foreach ($serviceNames as $serviceName) {
            $service = GovServices::findOne(['name' => $serviceName]);
            if ($service === null) {
                throw new Exception('Not found gov_service ' . $serviceName);
            }

            $gsp1 = new GovServicesParams();
            $gsp1->id_param = $param1->id;
            $gsp1->id_service = $service->id;
            $gsp1->req_in = false;
            $gsp1->req_out = false;
            $gsp1->flag_in = false;
            $gsp1->flag_out = true;
            $gsp1->sort_by = 98;

            if (!$gsp1->save()) {
                throw new Exception('Error saving gov_services_param 1 for service ' . $service->id);
            }

            $gsp2 = new GovServicesParams();
            $gsp2->id_param = $param2->id;
            $gsp2->id_service = $service->id;
            $gsp2->req_in = false;
            $gsp2->req_out = false;
            $gsp2->flag_in = false;
            $gsp2->flag_out = true;
            $gsp2->sort_by = 99;

            if (!$gsp2->save()) {
                throw new Exception('Error saving gov_services_param 2 for service ' . $service->id);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200819_011528_2963_add_vsd_params cannot be reverted.\n";

        return false;
    }
}
