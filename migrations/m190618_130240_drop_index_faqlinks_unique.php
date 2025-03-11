<?php

use app\commands\migrate\Migration;

/**
 * Class m190618_130240_drop_index_faqlinks_unique
 */
class m190618_130240_drop_index_faqlinks_unique extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('faq_links__text_uniq_index', 'faq_links');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190618_130240_drop_index_faqlinks_unique cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190618_130240_drop_index_faqlinks_unique cannot be reverted.\n";

        return false;
    }
    */
}
