<?php

use app\commands\migrate\Migration;

/**
 * Class m190513_075305_fix_mosru_stat
 */
class m190513_075305_fix_mosru_stat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
SELECT 
    count(DISTINCT visit_id), v.id_organization, log.log_time::date AS date 
FROM etp.status_log AS log
JOIN visits AS v ON v.id = log.visit_id
WHERE 
    log.etp_status = :status8021 OR log.etp_status = :status1053
GROUP BY v.id_organization, log.log_time::date
SQL;

        $rows = $this->getDb()
            ->createCommand($sql, [
                'status8021' => \app\modules\soap\models\etp\status\Status8021::CODE,
                'status1053' => \app\modules\soap\models\etp\status\Status1053::CODE
            ])
            ->queryAll();

        foreach ($rows as $row) {
            $this->update(
                'statistic.mosru_organizations_stat',
                [
                    'moved' => $row['count']
                ],
                [
                    'id_organization' => $row['id_organization'],
                    'date' => $row['date']
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190513_075305_fix_mosru_stat cannot be reverted.\n";

        return false;
    }
}
