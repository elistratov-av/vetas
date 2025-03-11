<?php

use app\commands\migrate\Migration;

class m221025_010363_add_fields_to_aviary extends Migration
{
    public function safeUp()
    {
        $this->addColumn('aviary', 'number', $this->string()->defaultValue(null));
        $this->addColumn('aviary', 'description', $this->string()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('aviary', 'number');
        $this->dropColumn('aviary', 'description');
    }
}
