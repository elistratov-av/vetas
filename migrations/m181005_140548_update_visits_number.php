<?php

use app\commands\migrate\Migration;

/**
 * Class m181005_140548_update_visits_number
 */
class m181005_140548_update_visits_number extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-visits-date_channel_organization_number', 'visits');
        $this->dropIndex('idx-visits-date_channel_organization', 'visits');
    }

}
