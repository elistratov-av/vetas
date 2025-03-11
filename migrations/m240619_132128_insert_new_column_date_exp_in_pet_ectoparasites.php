<?php

use app\commands\migrate\Migration;

/**
 * Class m240619_132128_insert_new_column_date_exp_in_pet_ectoparasites
 */
class m240619_132128_insert_new_column_date_exp_in_pet_ectoparasites extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addColumn('public.pet_ectoparasites', 'date_exp', $this->date()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropColumn('public.pet_ectoparasites', 'date_exp');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240619_132128_insert_new_column_date_exp_in_pet_ectoparasites cannot be reverted.\n";

        return false;
    }
    */
}
