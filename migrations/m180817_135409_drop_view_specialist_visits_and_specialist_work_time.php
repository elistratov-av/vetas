<?php

use app\commands\migrate\Migration;

/**
 * Class m180817_135409_drop_view_specialist_visits_and_specialist_work_time
 */
class m180817_135409_drop_view_specialist_visits_and_specialist_work_time extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP VIEW public.specialist_visits_range');
        $this->execute('DROP VIEW public.specialist_work_time;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180917_144338_drop_view_specialist_visits cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180917_144338_drop_view_specialist_visits cannot be reverted.\n";

        return false;
    }
    */
}
