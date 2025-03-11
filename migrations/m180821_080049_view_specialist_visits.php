<?php

use yii\db\Migration;

/**
 * Class m180821_080049_view_specialist_visits
 */
class m180821_080049_view_specialist_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = '
 CREATE OR REPLACE VIEW public.specialist_visits_range AS 
 SELECT visits_specialists.id_specialist,
    visits.id,
    visits.status,
    to_timestamp(visits.start_dttm::double precision)::date AS date,
    to_timestamp(visits.start_dttm::double precision) AS start_time,
    to_timestamp((visits.start_dttm + visits.duration * 60)::double precision) AS end_time,
    tsrange(
        to_timestamp(visits.start_dttm::double precision)::timestamp without time zone,
        to_timestamp((visits.start_dttm + visits.duration * 60 + coalesce(visits.cooldown, 0) * 60)::double precision)::timestamp without time zone,
        \'[)\'::text
    ) AS visits_range
   FROM visits
     JOIN visits_specialists ON visits_specialists.id_visit = visits.id;';

        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.specialist_visits_range 
        IS 'Временная view со списком занятых диапазонов у специалиста';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP VIEW public.specialist_visits');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180821_080049_view_specialist_visits cannot be reverted.\n";

        return false;
    }
    */
}
