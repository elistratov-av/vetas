<?php

use app\commands\migrate\Migration;

/**
 * Class m230309_140800_visits_table_add_guid_video
 */
class m230309_140800_visits_table_add_guid_video extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.visits', 'guid_video', $this->string()->defaultValue(null));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.visits', 'guid_video');

        return true;
    }
}
