<?php

use app\commands\migrate\Migration;

/**
 * Class m190604_104735_faq_links_add_field_target_blank
 */
class m190604_104735_faq_links_add_field_target_blank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('faq_links',
            'target_blank',
            $this->boolean()->notNull()->defaultValue(false)
        );

        $this->addCommentOnColumn('faq_links', 'target_blank', 'Открывать в новой вкладке');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('faq_links', 'target_blank');
    }
}
