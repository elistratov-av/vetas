<?php

use app\commands\migrate\Migration;

/**
 * Class m230516_084035_add_valid_until_column_to_model_PetDehelmintization
 */
class m230516_084035_add_valid_until_column_to_model_PetDehelmintization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_dehelmintization', 'valid_until', $this->date()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_dehelmintization', 'valid_until');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230516_084035_add_valid_until_column_to_model_PetDehelmintization cannot be reverted.\n";

        return false;
    }
    */
}
