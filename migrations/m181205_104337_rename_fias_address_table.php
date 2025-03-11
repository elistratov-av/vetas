<?php

use app\commands\migrate\Migration;

/**
 * Class m181205_104337_rename_fias_address_table
 */
class m181205_104337_rename_fias_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE public.fias_address RENAME TO fias_addresses');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE public.fias_addresses RENAME TO fias_address');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181205_104337_rename_fias_address_table cannot be reverted.\n";

        return false;
    }
    */
}
