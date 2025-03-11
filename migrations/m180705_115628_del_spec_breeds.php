<?php

use yii\db\Migration;

/**
 * Class m180705_115628_del_spec_breeds
 */
class m180705_115628_del_spec_breeds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('species_breeds');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180705_115628_del_spec_breeds cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180705_115628_del_spec_breeds cannot be reverted.\n";

        return false;
    }
    */
}
