<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\Species;

/**
 * Class m210201_084144_new_gov_services
 */
class m210201_084144_new_gov_services extends Migration
{
    private const SERVICE_0498 = [
        'cod' => '0498',
        'name' => 'Вакцинация собак',
        'alternative_name' => 'Вакцинация собак',
        'price' => 900,
    ];

    private const SERVICE_0499 = [
        'cod' => '0499',
        'name' => 'Вакцинация кошек',
        'alternative_name' => 'Вакцинация кошек',
        'price' => 850,
    ];

    private const TEMPLATE_COD = '0206';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * 1. Найти услугу 0206 (актуальную, deleted = false)
         * 2. Создать копии с данными
         *      0498 Вакцинация собак
         *      0499 Вакцинация кошек
         * 3. связь species_services с новыми услугами
         * 4. поля services_description_types скопировать с 0206
         * 5. Скопировать gov_services_reports
         * 6. Скопировать gov_services_params
         */

        $template = $this->getTemplateService();

        // 0498 Вакцинация собак
        $service_0498 = new GovServices(
            array_merge($template, self::SERVICE_0498)
        );
        $service_0498->save();

        // 0499 Вакцинация кошек
        $service_0499 = new GovServices(
            array_merge($template, self::SERVICE_0499)
        );
        $service_0499->save();


        // связи с породами
        $this->insert('species_services', [
            'id_species' => $this->getSpeciesId('DOG'),
            'id_service' => $service_0498->id
        ]);

        $this->insert('species_services', [
            'id_species' => $this->getSpeciesId('CAT'),
            'id_service' => $service_0499->id
        ]);

        // Описания
        $description_template = $this->getTemplateDescriptions();

        $this->insertRows(
            'services_description_types',
            $description_template,
            $service_0498->id
        );

        $this->insertRows(
            'services_description_types',
            $description_template,
            $service_0499->id
        );

        // gov_services_params
        $gov_services_params = $this->getTemplateGovServiceParams();
        $this->insertRows(
            'gov_services_params',
            $gov_services_params,
            $service_0498->id
        );

        $this->insertRows(
            'gov_services_params',
            $gov_services_params,
            $service_0499->id
        );

        // gov_services_reports
        $gov_services_reports = $this->getTemplateGovServiceReports();
        $this->insertRows(
            'gov_services_reports',
            $gov_services_reports,
            $service_0498->id
        );

        $this->insertRows(
            'gov_services_reports',
            $gov_services_reports,
            $service_0499->id
        );
    }

    protected function insertRows($table, $rows, $id_for_replace)
    {
        foreach ($rows as $row) {
            unset($row['id']);
            $this->insert($table,
                array_merge(
                    $row,
                    [
                        'id_service' => $id_for_replace
                    ]
                ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->deleteServiceAndData(self::SERVICE_0498['cod']);
        $this->deleteServiceAndData(self::SERVICE_0499['cod']);
    }

    /**
     * @return array|null
     */
    protected function getTemplateService()
    {
        $template = GovServices::findOne([
            'cod' => self::TEMPLATE_COD,
            'deleted' => false
        ])->getOldAttributes();

        // очищаем некоторые поля
        unset(
            $template['id'],
            $template['created_at'],
            $template['created_by'],
            $template['updated_by'],
            $template['updated_at']
        );

        return $template;
    }

    /**
     * @param $tech_name
     * @return int
     */
    protected function getSpeciesId($tech_name)
    {
        return Species::findOne([
            'tech_name' => $tech_name,
        ])->id;
    }

    /**
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    protected function getTemplateDescriptions()
    {
        $db = $this->getDb();
        return $db->createCommand("
    SELECT
        sdt.id_description_type,
        sdt.required 
    FROM 
         gov_services gs
    LEFT JOIN 
        services_description_types sdt on sdt.id_service = gs.id
    WHERE 
        gs.cod = :code", [
            ':code' => self::TEMPLATE_COD
        ])->queryAll();
    }

    /**
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    protected function getTemplateGovServiceParams()
    {
        $db = $this->getDb();
        return $db->createCommand("
    SELECT
        gsp.*
    FROM 
         gov_services gs
    LEFT JOIN 
        gov_services_params gsp on gsp.id_service = gs.id
    WHERE 
        gs.cod = :code", [
            ':code' => self::TEMPLATE_COD
        ])->queryAll();
    }

    /**
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    protected function getTemplateGovServiceReports()
    {
        $db = $this->getDb();
        return $db->createCommand("
    SELECT
        gsr.*
    FROM 
         gov_services gs
    LEFT JOIN 
        gov_services_reports gsr on gsr.id_service = gs.id
    WHERE 
        gs.cod = :code", [
            ':code' => self::TEMPLATE_COD
        ])->queryAll();
    }

    /**
     * @param $cod
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteServiceAndData($cod)
    {
        $service = GovServices::findOne([
            'cod' => $cod,
        ]);


        $this->delete('services_description_types', [
            'id_service' => $service->id,
        ]);

        $this->delete('species_services', [
            'id_service' => $service->id,
        ]);

        $this->delete('gov_services_reports', [
            'id_service' => $service->id,
        ]);

        $this->delete('gov_services_params', [
            'id_service' => $service->id,
        ]);

        $service->delete();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210201_084144_new_gov_services cannot be reverted.\n";

        return false;
    }
    */
}
