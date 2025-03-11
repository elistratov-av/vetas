<?php

use yii\db\Migration;

/**
 * Class m180628_092543_add_schedule_field_to_organizations_table
 */
class m180628_092543_add_schedule_field_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'schedule', $this->json());
        $this->addCommentOnColumn('organizations', 'schedule', 'Расписание работы организаций');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'schedule');
    }
}
