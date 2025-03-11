<?php

use yii\db\Migration;

/**
 * Handles adding actual_mailing_date to table `newsletter_info`.
 */
class m231021_110812_add_actual_mailing_date_column_to_newsletter_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('newsletter_info', 'actual_mailing_date', 'timestamp without time zone');
        $this->addCommentOnColumn('newsletter_info', 'actual_mailing_date', 'Фактическая дата рассылки');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('newsletter_info', 'actual_mailing_date');
    }
}
