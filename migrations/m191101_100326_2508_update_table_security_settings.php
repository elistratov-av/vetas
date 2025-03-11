<?php

use app\commands\migrate\Migration;

/**
 * Class m191101_100326_2508_update_table_security_settings
 */
class m191101_100326_2508_update_table_security_settings extends Migration
{
    private $tableName = 'admin.security_settings';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'external_services_auth_enabled', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'external_services_auth_enabled');
    }
}
