<?php

use app\commands\migrate\Migration;

/**
 * Class m200310_034112_2674_fix_elk_owners_empty_phones
 */
class m200310_034112_2674_fix_elk_owners_empty_phones extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('elk.owners', ['phone' => null], ['phone' => '+7']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200310_034112_2674_fix_elk_owners_empty_phones cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200310_034112_2674_fix_elk_owners_empty_phones cannot be reverted.\n";

        return false;
    }
    */
}
