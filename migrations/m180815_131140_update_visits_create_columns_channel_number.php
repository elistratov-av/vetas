<?php

use yii\db\Migration;

/**
 * Class m180815_131140_update_visits_create_columns_channel_number
 */
class m180815_131140_update_visits_create_columns_channel_number extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'channel', $this->integer()->defaultValue(0));
        $this->addColumn('visits', 'number', $this->integer()->defaultValue(0));
//        $this->execute('ALTER TABLE visits ALTER COLUMN channel SET NOT NULL');
//        $this->execute('ALTER TABLE visits ALTER COLUMN number SET NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'channel');
        $this->dropColumn('visits', 'number');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_131140_update_visits_create_columns_channel_number cannot be reverted.\n";

        return false;
    }
    */
}
