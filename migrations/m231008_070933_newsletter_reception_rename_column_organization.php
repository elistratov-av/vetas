<?php

use app\commands\migrate\Migration;

class m231008_070933_newsletter_reception_rename_column_organization extends Migration
{
    private const TABLE = 'newsletter_reception';

    public function safeUp()
    {
        $this->renameColumn(self::TABLE, 'organizations', 'organization_id');
    }

    public function safeDown()
    {
        $this->renameColumn(self::TABLE, 'organization_id', 'organizations');
    }
}
