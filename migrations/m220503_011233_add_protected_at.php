<?php

use app\commands\migrate\Migration;

/**
 * Class m220503_011233_add_protected_at
 */
class m220503_011233_add_protected_at extends Migration
{
    public function safeUp()
    {
        $this->addColumn('public.documents', 'protected_at', $this->dateTime()->null());
        $this->addColumn('public.pet_health', 'protected_at', $this->dateTime()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('public.documents', 'protected_at');
        $this->dropColumn('public.pet_health', 'protected_at');
    }

}
