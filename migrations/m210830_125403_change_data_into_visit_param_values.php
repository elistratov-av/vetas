<?php

use app\commands\migrate\Migration;

/**
 * Class m210830_125403_change_data_into_visit_param_values
 */
class m210830_125403_change_data_into_visit_param_values extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $visitParamValues = $this->getVisitParamValues();

        foreach ($visitParamValues as $visitParamValue) {
            $this->update(
                'visit_param_values',
                ['date_value' => (new DateTime($visitParamValue['fact_start_dttm']))->getTimestamp()],
                ['id' => $visitParamValue['id']]
            );
        }
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    private function getVisitParamValues(): array
    {
        $paramId = $this->findIdParamByTechName('P3_Visitstartdate');

        if (!$paramId) {
            return [];
        }

        return Yii::$app->db->createCommand("
           SELECT visit_param_values.id, visit_param_values.date_value, visits.fact_start_dttm
                FROM visit_param_values
                         INNER JOIN visits on visit_param_values.id_visit = visits.id
                WHERE visit_param_values.id_param = $paramId
                  AND visit_param_values.date_value is null
                  AND visits.fact_start_dttm is not null
        ")->queryAll();
    }

    /**
     * @param $techName
     * @return false|int|string|\yii\db\DataReader|null
     * @throws \yii\db\Exception
     */
    private function findIdParamByTechName($techName)
    {
        return Yii::$app->db->createCommand("
                SELECT id
                FROM params
                WHERE tech_name = '$techName'"
        )->queryScalar();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210830_125403_change_data_into_visit_param_values cannot be reverted.\n";

        return false;
    }
}
