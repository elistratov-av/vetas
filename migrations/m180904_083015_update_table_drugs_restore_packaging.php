<?php

use yii\db\Migration;

/**
 * Class m180904_083015_update_table_drugs_restore_packaging
 */
class m180904_083015_update_table_drugs_restore_packaging extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('drugs', 'packaging', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('drugs', 'packaging');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180904_083015_update_table_drugs_restore_packaging cannot be reverted.\n";

        return false;
    }
    */
}
