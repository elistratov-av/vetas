<?php

use app\commands\migrate\Migration;

/**
 * Class m200325_065030_2656_add_tgrm_support_pets
 */
class m200325_065030_2656_add_tgrm_support_pets extends Migration
{
    private $tableName = 'public.pets';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('create index "idx_' . 'pets' . '_name_gin" on ' . $this->tableName . ' using gin ("name" gin_trgm_ops);');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200325_065030_2656_add_tgrm_support_pets cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200325_065030_2656_add_tgrm_support_pets cannot be reverted.\n";

        return false;
    }
    */
}
