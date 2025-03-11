<?php

use yii\db\Migration;

/**
 * Class m180627_100953_addresses_columns
 */
class m180627_100953_addresses_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE addresses ADD COLUMN IF NOT EXISTS id_area INTEGER');
        $this->execute('ALTER TABLE addresses ADD COLUMN IF NOT EXISTS id_district INTEGER');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180627_100953_addresses_columns cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180627_100953_addresses_columns cannot be reverted.\n";

        return false;
    }
    */
}
