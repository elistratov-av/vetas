<?php

use yii\db\Migration;

/**
 * Handles adding preferences to table `visits`.
 */
class m200805_065330_add_preferences_columns_to_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'is_veteran', $this->boolean()->notNull()->defaultValue(false));
        $this->addCommentOnColumn('visits', 'is_veteran', 'Ветеран ВОВ');
        $this->addColumn('visits', 'is_disabled', $this->boolean()->notNull()->defaultValue(false));
        $this->addCommentOnColumn('visits', 'is_disabled', 'Инвалид I группы');
        $this->addColumn('visits', 'is_blind', $this->boolean()->notNull()->defaultValue(false));
        $this->addCommentOnColumn('visits', 'is_blind', 'Слабовидящий с животным поводырем');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCommentFromColumn('visits', 'is_blind');
        $this->dropColumn('visits', 'is_blind');
        $this->dropCommentFromColumn('visits', 'is_disabled');
        $this->dropColumn('visits', 'is_disabled');
        $this->dropCommentFromColumn('visits', 'is_veteran');
        $this->dropColumn('visits', 'is_veteran');
    }
}
