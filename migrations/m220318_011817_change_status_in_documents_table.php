<?php

use app\commands\migrate\Migration;

/**
 * Class m220318_011817_change_status_in_documents_table
 */
class m220318_011817_change_status_in_documents_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('public.documents', 'status', 'DROP NOT NULL');
    }

    public function safeDown()
    {
        $this->alterColumn('public.documents', 'status', 'SET NOT NULL');
    }

}
