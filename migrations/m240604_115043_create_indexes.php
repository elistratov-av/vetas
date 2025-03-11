<?php

use app\commands\migrate\Migration;

/**
 * Class m240604_115043_create_indexes
 */
class m240604_115043_create_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP INDEX IF EXISTS idx_visits_fact_start_dttm_lower_time_range");
        $this->execute("DROP INDEX IF EXISTS idx_visits_lower_time_range");
        $this->execute("CREATE INDEX idx_visits_fact_start_dttm_lower_time_range ON visits(coalesce(fact_start_dttm::date, lower(time_range)::date))");
        $this->execute("CREATE INDEX idx_visits_lower_time_range ON visits(LOWER(time_range))
    ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP INDEX IF EXISTS idx_visits_fact_start_dttm_lower_time_range");
        $this->execute("DROP INDEX IF EXISTS idx_visits_lower_time_range");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240604_115043_create_indexes cannot be reverted.\n";

        return false;
    }
    */
}
