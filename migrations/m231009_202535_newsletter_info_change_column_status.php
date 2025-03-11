<?php

use app\commands\migrate\Migration;

class m231009_202535_newsletter_info_change_column_status extends Migration
{
    private const TABLE = 'newsletter_info';

    public function safeUp()
    {
        $this->dropColumn(self::TABLE, 'status');
        $this->addColumn(self::TABLE, 'status', $this->integer()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE, 'status');
        $this->addColumn(self::TABLE, 'status', $this->boolean());
    }
}
