<?php

use app\commands\migrate\Migration;

/**
 * Class m181122_115904_add_count_rows_to_organizations_emergency
 */
class m181122_115904_add_count_rows_to_organizations_emergency extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations_emergency', 'count_live_queue_visits', $this->integer()->defaultValue(0));
        $this->addCommentOnColumn('organizations_emergency', 'count_live_queue_visits', 'Количество измененных визитов из живой очереди');
        $this->addColumn('organizations_emergency', 'count_not_live_queue_visits', $this->integer()->defaultValue(0));
        $this->addCommentOnColumn('organizations_emergency', 'count_not_live_queue_visits', 'Количество измененных визитов НЕ из живой очереди');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations_emergency', 'count_live_queue_visits');
        $this->dropColumn('organizations_emergency', 'count_not_live_queue_visits');
    }
}
