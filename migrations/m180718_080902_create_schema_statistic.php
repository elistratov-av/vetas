<?php

use yii\db\Migration;

/**
 * Class m180718_080902_create_schema_statistic
 */
class m180718_080902_create_schema_statistic extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA statistic;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP SCHEMA statistic;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180718_080902_create_schema_statistic cannot be reverted.\n";

        return false;
    }
    */
}
