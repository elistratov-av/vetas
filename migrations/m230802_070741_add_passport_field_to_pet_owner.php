<?php

use app\commands\migrate\Migration;

/**
 * Class m230802_070741_add_passport_field_to_pet_owner
 */
class m230802_070741_add_passport_field_to_pet_owner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230802_070741_add_passport_field_to_pet_owner cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $this->addColumn('pet_owners', 'passport', $this->string());
    }

    public function down()
    {
        $this->dropColumn('pet_owners', 'passport');
    }
}
