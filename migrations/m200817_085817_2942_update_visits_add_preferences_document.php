<?php

use app\commands\migrate\Migration;

/**
 * Class m200817_085817_2942_update_visits_add_preferences_document
 */
class m200817_085817_2942_update_visits_add_preferences_document extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.visits', 'preferences_document', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.visits', 'preferences_document');
    }
}
