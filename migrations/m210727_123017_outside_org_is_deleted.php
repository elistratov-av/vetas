<?php

use app\commands\migrate\Migration;

/**
 * Class m210727_123017_outside_org_is_deleted
 */
class m210727_123017_outside_org_is_deleted extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.outside_org',
            'is_deleted',
            $this->boolean()->notNull()->defaultValue('false')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.outside_org',
            'is_deleted'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210727_123017_outside_org_is_deleted cannot be reverted.\n";

        return false;
    }
    */
}
