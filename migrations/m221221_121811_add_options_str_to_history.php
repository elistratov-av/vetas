<?php

use app\commands\migrate\Migration;

/**
 * Class m221221_121811_add_options_str_to_history
 */
class m221221_121811_add_options_str_to_history extends Migration
{
    public function safeUp()
    {
        $this->addColumn('pet_history', 'options_str', $this->string()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('pet_history', 'options_str');
    }

}
