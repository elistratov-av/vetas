<?php

use app\commands\migrate\Migration;

/**
 * Class m190522_131731_change_elk_pet_sex_field
 */
class m190522_131731_change_elk_pet_sex_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('elk.pets', 'sex', $this->string(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
