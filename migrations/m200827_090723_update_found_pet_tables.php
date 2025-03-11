<?php

use app\commands\migrate\Migration;

/**
 * Class m200827_090723_update_found_pet_tables
 */
class m200827_090723_update_found_pet_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('found_pet.ad_authors', 'sso_id', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200827_090723_update_found_pet_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200827_090723_update_found_pet_tables cannot be reverted.\n";

        return false;
    }
    */
}
