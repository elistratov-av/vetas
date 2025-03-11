<?php

use app\commands\migrate\Migration;

/**
 * Class m190620_084703_drop_drugs_tmp
 */
class m190620_084703_drop_drugs_tmp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('drop table if exists drugs_tmp');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190620_084703_drop_drugs_tmp cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190620_084703_drop_drugs_tmp cannot be reverted.\n";

        return false;
    }
    */
}
