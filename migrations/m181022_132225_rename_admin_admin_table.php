<?php

use app\commands\migrate\Migration;

/**
 * Class m181022_132225_rename_admin_admin_table
 */
class m181022_132225_rename_admin_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE "admin"."admin" RENAME TO "users"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE "admin"."users" RENAME TO "admin"');
    }
}
