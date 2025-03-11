<?php

use app\commands\migrate\Migration;

class m231010_115338_newsletter_info_add_column_mailing_time_option extends Migration
{
    private const TABLE = 'newsletter_info';

    public function safeUp()
    {
        $this->addColumn(self::TABLE, 'mailing_time_option', $this->integer()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE, 'mailing_time_option');
    }
}
