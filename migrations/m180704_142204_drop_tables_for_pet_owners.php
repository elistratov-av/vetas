<?php

use yii\db\Migration;

/**
 * Class m180704_142204_drop_tables_for_pet_owners
 */
class m180704_142204_drop_tables_for_pet_owners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('jur_persons');
        $this->dropTable('nat_persons');
        $this->dropTable('owners');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180704_142204_drop_tables_for_pet_owners cannot be reverted.\n";

        return false;
    }
}
