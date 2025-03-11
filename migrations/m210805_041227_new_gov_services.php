<?php

use app\commands\migrate\Migration;
use app\models\db\ActiveRecord;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\GovServicesReports;
use app\models\db\Reports;
use app\models\db\ReportsParams;
use app\models\db\ServicesDescriptionTypes;
use app\models\db\DescriptionTypes;
use yii\helpers\Console;

/**
 * Class m210805_041227_new_gov_services
 */
class m210805_041227_new_gov_services extends Migration
{
    //Добавляемые услуги. Отличаются только названием, кодом, параметром и ценой.
    private $services = [
        [
            'name'     => 'Общий анализ мочи: Исследование мочи на креатинин',
            'cod'      => '0480',
            'id_param' => 202,
            'price'    => 122
        ],
        [
            'name'     => 'Общий анализ мочи: Исследование мочи на мочевину',
            'cod'      => '0481',
            'id_param' => 166,
            'price'    => 122
        ],
        [
            'name'     => 'Общий анализ мочи: Исследование мочи на глюкозу',
            'cod'      => '0482',
            'id_param' => 274,
            'price'    => 122
        ],
        [
            'name'     => 'Общий клинический анализ крови: Подсчет ретикулоцитов',
            'cod'      => '0483',
            'id_param' => null,
            'price'    => 276
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * ПАРАМАТЕР ДЛЯ ПОДСЧЕТА РЕТИКУЛОЦИТОВ
         */
        $new_param = new \app\models\db\Params([
            'name'             => 'Подсчет ретикулоцитов',
            'tech_name'        => 'P100_Reticulocytesvalue',
            'datatype'         => 'text',
            'datatype_details' => '15',
        ]);
        if (!$new_param->save()) {
            Console::output('Failed to save new param');

            return false;
        };
        $this->services[array_key_last($this->services)]['id_param'] = $new_param->id;

        /*
         * ШАБЛОНЫ КОНФИГОВ И ПАРАМЕТРОВ
         */
        $gov_service_config =
            [
                'id_pricelist'       => 14,
                'price'              => 122,
                'sort_by'            => null,
                'id_service_type'    => 5, // Лабораторные исследования
                'id_specialization'  => 11,
                'duration'           => 10,
                'id_service_measure' => 10, // исследование
                'cooldown'           => 10,
                'id_service_goal'    => null,
                'at_home'            => false, // Значение по умолчанию
                'type'               => null,
                'at_clinic'          => true,  // Значение по умолчанию
                'deleted'            => false, // Значение по умолчанию
                'briefname'          => null,
                'com_class_journal'  => null,
                'for_broods'         => GovServices::FOR_NULL,
                'for_multiple'       => GovServices::FOR_HEAD,
                'once_per_day'       => false,
            ];
        $report_config = [
            'id'          => 40,
            'name'        => 'Результат клинического анализа глюкозы в моче',
            'report_type' => 'R',
            'grouped'     => false,
            'sending'     => true
        ];
        $description_tech_names = [
            'VISIT_ANAMNEZ_1',
            'VISIT_DATA_ZABOLEVANIYA',
            'VISIT_ZAKLYUCHENIE',
            'VISIT_CLINICAL_DATA',
        ];

        $service_params = [
            [
                'req_in'   => false,
                'req_out'  => false,
                'sort_by'  => 1,
                'flag_in'  => false,
                'flag_out' => true
            ],
        ];
        $report_params = [
            [
                'id_report' => null
            ]
        ];

        $description_params = [];
        // достаем id для сущностей по ее $tech_name и формируем список
        foreach ($description_tech_names as $tech_name) {
            $descriptionType = DescriptionTypes::find()
                ->where(['tech_name' => $tech_name])
                ->one();

            if (!$descriptionType) {
                continue;
            }

            $description_params[] = [
                'id_description_type' => $descriptionType->id,
                'required'            => false,
            ];
        }

        /*
         * ОСНОВНОЙ ЦИКЛ
         */
        foreach ($this->services as $service) {
            //Новая услуга
            $gov_service_config['name'] = $gov_service_config['alternative_name'] = $service['name'];
            $gov_service_config['cod'] = $service['cod'];
            $gov_service = new GovServices($gov_service_config);
            if (!$gov_service->save()) {
                echo 'Failed to create new service';

                return false;
            }

            //Связь с описаниями
            if ($description_params) {
                foreach ($description_params as &$description_param) {
                    $description_param['id_service'] = $gov_service->id;
                }
                $this->addParams($description_params, ServicesDescriptionTypes::class);
            }

            //Связь с параметрами услуги
            foreach ($service_params as &$service_param) {
                $service_param['id_param'] = $service['id_param'];
                $service_param['id_service'] = $gov_service->id;
            }
            $this->addParams($service_params, GovServicesParams::class);

            //Создаем отчет
            $report_config['name'] = $service['name'];
            $report = new Reports($report_config);
            if (!$report->save()) {
                echo 'Failed to create new report';

                return false;
            }
            $report_config['id']++;

            //Связь с полями отчета
            foreach ($report_params as &$report_param) {
                $report_param['id_param'] = $service['id_param'];
                $report_param['id_report'] = $report->id;
            }
            $this->addParams($report_params, ReportsParams::class);

            //Связь услуги и отчета
            $services_reports_params = [
                [
                    'id_report'  => $report->id,
                    'id_service' => $gov_service->id
                ]
            ];
            $this->addParams($services_reports_params, GovServicesReports::class);
        }
    }

    public function safeDown()
    {
        echo 'cannot be reverted';

        return false;
    }

    protected function addParams($params, $target)
    {
        foreach ($params as $config) {
            /** @var ActiveRecord $target_entity */
            $target_entity = new $target($config);
            if (!$target_entity->save()) {
                throw new \yii\base\Exception('Failed to save ' . $target_entity::tableName());
            }
        }

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210805_041227_new_gov_services cannot be reverted.\n";

        return false;
    }
    */
}
