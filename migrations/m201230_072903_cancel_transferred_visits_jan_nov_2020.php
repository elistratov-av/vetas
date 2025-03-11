<?php

use app\commands\migrate\Migration;

/**
 * Class m201230_072903_cancel_transferred_visits_jan_nov_2020
 */
class m201230_072903_cancel_transferred_visits_jan_nov_2020 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('visits', ['status' => 'A'], [
            'and',
                ['status' => 'T'],
                [
                    'between', 'coalesce(fact_start_dttm, start_dttm, created_at)', '2020-01-01', '2020-12-01'
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201230_072903_cancel_transferred_visits_jan_nov_2020 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201230_072903_cancel_transferred_visits_jan_nov_2020 cannot be reverted.\n";

        return false;
    }
    */
}
