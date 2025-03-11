<?php

use yii\db\Migration;

/**
 * Class m180621_132830_id_address
 */
class m180621_132830_id_address extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'id_address', 'integer');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180621_132830_id_address cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180621_132830_id_address cannot be reverted.\n";

        return false;
    }
    */
}
