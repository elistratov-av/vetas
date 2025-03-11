<?php

use app\commands\migrate\Migration;

/**
 * Class m221201_000001_add_fullname_to_pet_owners_tmp
 */
class m221201_000001_add_fullname_to_pet_owners_tmp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners_tmp', 'fullname', $this->string(255));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owners_tmp', 'fullname');

        return true;
    }
}
