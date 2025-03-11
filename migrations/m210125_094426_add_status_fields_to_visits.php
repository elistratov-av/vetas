<?php

use app\commands\migrate\Migration;

/**
 * Class m210125_094426_add_status_fields_to_visits
 */
class m210125_094426_add_status_fields_to_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'is_orphan', $this->boolean()->defaultValue(false));
        $this->addColumn('visits', 'is_large_family', $this->boolean()->defaultValue(false));
        $this->addColumn('visits', 'is_veteran_of_labour', $this->boolean()->defaultValue(false));
        $this->addCommentOnColumn('visits', 'is_orphan', 'Дети-сироты, дети, оставшиеся без попечения родителей в возрасте до 23 лет');
        $this->addCommentOnColumn('visits', 'is_large_family', 'Многодетные семьи');
        $this->addCommentOnColumn('visits', 'is_veteran_of_labour', 'Ветеран труда');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits','is_orphan');
        $this->dropColumn('visits','is_large_family');
        $this->dropColumn('visits','is_veteran_of_labour');
    }
}
