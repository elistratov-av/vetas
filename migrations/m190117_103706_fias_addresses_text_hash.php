<?php

use app\commands\migrate\Migration;

/**
 * Class m190117_103706_fias_addresses_text_hash
 */
class m190117_103706_fias_addresses_text_hash extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('fias_addresses','text_hash', $this->string(32));

        // Считаем хеш
        $query = \app\models\db\FiasAddresses::find();

        foreach ($query->each() as $address) {
            if($address->save() !=  TRUE){
                var_dump($address->errors);
                die();
            }
        }

        $this->createIndex('idx_fias_addresses_text_hash', 'fias_addresses','text_hash');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropColumn('fias_addresses','text_hash');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190117_103706_fias_addresses_text_hash cannot be reverted.\n";

        return false;
    }
    */
}
