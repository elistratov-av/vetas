<?php

use yii\db\Migration;

/**
 * Handles adding fact_start_dttm_column_fact_end_dttm_column_cooldown_column_author_column_source to table `visits`.
 */
class m180801_140732_add_fields_to_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'fact_start_dttm', $this->integer());
        $this->addCommentOnColumn('visits', 'fact_start_dttm', 'Фактическое время начала визита');

        $this->addColumn('visits', 'fact_end_dttm', $this->integer());
        $this->addCommentOnColumn('visits', 'fact_end_dttm', 'Фактическое время окончания визита');

        $this->addColumn('visits', 'cooldown', $this->integer());
        $this->addCommentOnColumn('visits', 'cooldown', 'Продолжительность перерыва после приема');

        $this->addColumn('visits', 'id_specialist', $this->integer());
        $this->addCommentOnColumn('visits', 'id_specialist', 'Ссылка на специалиста, автора записи');
        $this->addForeignKey(
            'fk-visits-id_specialist',
            'visits',
            'id_specialist',
            'specialists',
            'id'
        );

        $this->addColumn('visits', 'id_visit', $this->integer());
        $this->addCommentOnColumn('visits', 'id_visit', 'Ссылка на визит (повторный прием и т.п.)');
        $this->addForeignKey(
            'fk-visits-id_visit',
            'visits',
            'id_visit',
            'visits',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'fact_start_dttm');
        $this->dropColumn('visits', 'fact_end_dttm');
        $this->dropColumn('visits', 'cooldown');
        $this->dropColumn('visits', 'id_specialist');
        $this->dropColumn('visits', 'id_visit');
    }
}
