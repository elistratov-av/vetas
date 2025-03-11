<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class CronController extends Controller
{
    /**
     * Calc and update statistic.gov_services_rating
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionCalcGovServicesRating()
    {
        $sql_truncate = 'TRUNCATE statistic.gov_services_rating;';
        $sql_calc = '
INSERT INTO
	statistic.gov_services_rating (sort_by, gov_service_id, visits_count)
SELECT 
    row_number() over (ORDER BY sub_query.visits_count DESC) AS sort_by,
    gov_service_id,
    visits_count
FROM (
  SELECT 
    gov_services.id AS gov_service_id,
    count(*) AS visits_count 
  FROM 
    gov_services
  LEFT JOIN 
    visits_gov_services ON visits_gov_services.id_service = gov_services.id
  GROUP BY 
    gov_services.id, visits_gov_services.id_service
  ORDER BY 
    visits_count DESC, name ASC
) AS sub_query
ORDER BY 
  sub_query.visits_count DESC';

        $db = Yii::$app->getDb();

        $db->beginTransaction();
        $db->createCommand($sql_truncate)->execute();
        $db->createCommand($sql_calc)->execute();

        $db->transaction->commit();

        return ExitCode::OK;

    }
}
