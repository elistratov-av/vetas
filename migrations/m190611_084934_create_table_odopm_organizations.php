<?php

use app\commands\migrate\Migration;

/**
 * Class m190611_084934_create_table_odopm_organizations
 */
class m190611_084934_create_table_odopm_organizations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE SCHEMA odopm");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP SCHEMA odopm');
    }

}
