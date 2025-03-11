<?php

use app\commands\migrate\Migration;

/**
 * Class m190325_134635_recalc_fias_hash
 */
class m190325_134635_recalc_fias_hash extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Считаем хеш
        $query = \app\models\db\FiasAddresses::find();

        foreach ($query->each() as $address) {
            if($address->save() !=  TRUE){
                var_dump($address->errors);
                die();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190325_134635_recalc_fias_hash cannot be reverted.\n";

        return false;
    }
    */
}
