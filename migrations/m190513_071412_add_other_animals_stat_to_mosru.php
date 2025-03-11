<?php

use app\commands\migrate\Migration;

/**
 * Class m190513_071412_add_other_animals_stat_to_mosru
 */
class m190513_071412_add_other_animals_stat_to_mosru extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'statistic.mosru_organizations_stat',
            'other_visits',
            $this->integer()->defaultValue(0)
        );
        $this->addColumn(
            'statistic.mosru_organizations_stat',
            'other_finished_visits',
            $this->integer()->defaultValue(0)
        );

        $this->updateStat();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('statistic.mosru_organizations_stat', 'other_visits');
        $this->dropColumn('statistic.mosru_organizations_stat', 'other_finished_visits');
    }

    protected function updateStat()
    {
        $sql = <<<SQL
SELECT count(*), v.id_organization, e.created_at::date AS date FROM etp.message AS e
JOIN visits AS v ON v.id = e.visit_id
JOIN pets AS p ON v.id_pet = p.id
WHERE 
    e.created_at::date BETWEEN '2019-04-08' AND (now() - make_interval(days => 1))::date
    AND p.id_species = :id_species
GROUP BY v.id_organization, e.created_at::date
SQL;

        $rows = $this->getDb()
            ->createCommand($sql, ['id_species' => \Yii::$app->params['mosru']['species_ids']['other']])
            ->queryAll();

        foreach ($rows as $row) {
            $this->update(
                'statistic.mosru_organizations_stat',
                [
                    'other_visits' => $row['count']
                ],
                [
                    'id_organization' => $row['id_organization'],
                    'date' => $row['date']
                ]
            );
        }

        $sql = <<<SQL
SELECT count(*), v.id_organization, log.log_time::date AS date FROM etp.status_log AS log
JOIN visits AS v ON v.id = log.visit_id
JOIN pets AS p ON v.id_pet = p.id
WHERE
    log.log_time::date BETWEEN '2019-04-08' AND (now() - make_interval(days => 1))::date 
    AND p.id_species = :id_species
    AND log.etp_status = :status
GROUP BY v.id_organization, log.log_time::date
SQL;

        $rows = $this->getDb()
            ->createCommand($sql, [
                'id_species' => \Yii::$app->params['mosru']['species_ids']['other'],
                'status' => \app\modules\soap\models\etp\status\Status1075::CODE
            ])
            ->queryAll();

        foreach ($rows as $row) {
            $this->update(
                'statistic.mosru_organizations_stat',
                [
                    'other_finished_visits' => $row['count']
                ],
                [
                    'id_organization' => $row['id_organization'],
                    'date' => $row['date']
                ]
            );
        }
    }
}
