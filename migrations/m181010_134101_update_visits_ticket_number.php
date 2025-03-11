<?php

use app\commands\migrate\Migration;

/**
 * Class m181010_134101_update_visits_ticket_number
 */
class m181010_134101_update_visits_ticket_number extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx-visits-date_channel_organization', 'visits');
        $this->dropIndex('idx-visits-date_channel_organization_number', 'visits');

        $sql = <<<SQL
update visits
set number = dc.rc
from (
  select id,
    row_number() over (partition by id_organization, channel, date_trunc('day', COALESCE(start_dttm, created_at))) as rc
  from visits
) as dc
where dc.id = visits.id
SQL;

        $this->execute($sql);
        $this->createIndex(
            'idx-visits-date_channel_organization_number',
            'visits',
            [
                'id_organization',
                'channel',
                new \yii\db\Expression("date_trunc('day', COALESCE(start_dttm, created_at))"),
                'number'
            ],
            true
        );

        $this->createIndex(
            'idx-visits-date_channel_organization',
            'visits',
            [
                'id_organization',
                'channel',
                new \yii\db\Expression("date_trunc('day', COALESCE(start_dttm, created_at))"),
            ]
        );

        /** @var \app\models\db\Visits[] $visits */
        $visits = \app\models\db\Visits::find()->all();
        foreach ($visits as $visit) {
            /** @var \app\common\components\ticket\TicketGeneratorInterface  $ticketGenerator */
            $ticketGenerator = \Yii::$container->get('app\common\components\ticket\TicketGeneratorInterface',
                [], [
                    'visit' => $visit
                ]);

            $visit->ticket_number = $ticketGenerator->make();
            $visit->save(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
