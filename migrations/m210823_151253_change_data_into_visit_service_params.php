<?php

use app\commands\migrate\Migration;

/**
 * Class m210823_151253_change_data_into_visit_service_params
 */
class m210823_151253_change_data_into_visit_service_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $visitServiceParams = $this->findVisitServiceParams();

        foreach ($visitServiceParams as $visitServiceParam) {
            $visitService = $this->findVisitGovService($visitServiceParam['id_visitservice']);
            if (!$visitService) {
                continue;
            }

            $this->update(
                "visit_service_param_values",
                [
                    "id_visit"     => $visitService['id_visit'],
                    "id_pet"       => $visitService['id_pet'] ?? null,
                    "migrate_flag" => 3,
                ],
                "id = {$visitServiceParam['id']}"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->update(
            "visit_service_param_values",
            [
                "id_visit" => null,
                "id_pet"   => null,
            ],
            "migrate_flag = 3"
        );
    }

    /*
    * @param $visitId
    * @return array
    * @throws \yii\db\Exception
    */
    private function findVisitGovService($visitServiceId): array
    {
        return Yii::$app->db->createCommand("
            select *
            from visits_gov_services
            where id = $visitServiceId
        ")->queryOne();
    }

    /*
    * @param $visitId
    * @return array
    * @throws \yii\db\Exception
    */
    private function findVisitServiceParams(): array
    {
        return Yii::$app->db->createCommand("
                select *
                from visit_service_param_values
                where id_param = 489 and id_visit is null
        ")->queryAll();
    }

}
