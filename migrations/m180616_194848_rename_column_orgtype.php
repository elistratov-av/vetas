<?php

use yii\db\Migration;

/**
 * Class m180616_194848_rename_column_orgtype
 */
class m180616_194848_rename_column_orgtype extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('org_types', 'id_org_type', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180616_194848_rename_column_orgtype cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180616_194848_rename_column_orgtype cannot be reverted.\n";

        return false;
    }
    */
}
