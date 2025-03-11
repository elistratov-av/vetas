<?php

use app\commands\migrate\Migration;

/**
 * Class m210730_100733_fill_with_data_visit_service_vaccination
 */
class m210730_100733_fill_with_data_visit_service_vaccination extends Migration
{
    /**
     * Пившем в таблицу visit_service_vaccination все недостающие данные
     * о проведенных вакцинациях
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tables = [
            'pet_other_vaccinations',
            'pet_rabies_vaccination',
        ];

        foreach ($tables as $tableName) {
            $vaccinationList = $this->findAllVaccination($tableName);
            if (!$vaccinationList) {
                continue;
            }

            foreach ($vaccinationList as $vaccination) {
                if ((bool)Yii::$app->db->createCommand("SELECT 1 FROM visit_service_vaccination WHERE id_visit_service_tmc = {$vaccination['id_visit_service_tmc']} ")->queryScalar()) {
                    continue;
                }

                if (empty($vaccination['id_visit_service_tmc'])) {
                    continue;
                }

                $visitServiceTmc = $this->findVisitServiceTmc($vaccination['id_visit_service_tmc']);
                if (!$visitServiceTmc || empty($visitServiceTmc['id_balance_tmc'])) {
                    continue;
                }

                $balance = $this->findBalance($visitServiceTmc['id_balance_tmc']);
                if (!$balance) {
                    continue;
                }

                $arrtibutes = [
                    'batch'                => $vaccination['batch'],
                    'production_date'      => $vaccination['production_date'],
                    'expiry_date'          => $vaccination['expiry_date'],
                    'date'                 => $vaccination['date'],
                    'valid_until'          => $vaccination['valid_until'],
                    'id_visit_service_tmc' => $vaccination['id_visit_service_tmc'],
                ];

                $this->insert('visit_service_vaccination', $arrtibutes);
            }
        }
    }

    public function findAllVaccination($tableName)
    {
        return Yii::$app->db->createCommand("
            SELECT * FROM $tableName WHERE id_visit_service_tmc is not null
        ")->queryAll();
    }

    private function findVisitServiceTmc($id)
    {
        return Yii::$app->db->createCommand("
            SELECT * FROM visit_service_tmc WHERE id = $id;
        ")->queryOne();
    }

    private function findBalance($id)
    {
        return Yii::$app->db->createCommand("
            SELECT * FROM tmc.balance WHERE id = $id;
        ")->queryOne();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210730_100733_fill_with_data_visit_service_vaccination cannot be reverted.\n";

        return false;
    }
}
