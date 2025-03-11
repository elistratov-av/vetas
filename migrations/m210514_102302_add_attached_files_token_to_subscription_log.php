<?php

use app\commands\migrate\Migration;

/**
 * Class m210514_102302_add_attached_files_token_to_subscription_log
 */
class m210514_102302_add_attached_files_token_to_subscription_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('subscription.log', 'attached_files_token', $this->string()->defaultValue(null)->comment('Токен для публичного доступа к файлам прикрепленного к оповещению'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('subscription.log', 'attached_files_token');
    }
}
