<?php

use app\commands\migrate\Migration;

/**
 * Class m210820_050717_set_valid_until_all_pet_rabies_vaccination
 */
class m210820_050717_set_valid_until_all_pet_rabies_vaccination extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('update pet_rabies_vaccination 
set valid_until = date + interval \'1 year\'
where valid_until is null
');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
