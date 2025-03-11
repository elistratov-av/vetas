<?php

use app\commands\migrate\Migration;

/**
 * Class m180827_041233_update_table_gov_services_add_cooldown
 */
class m180827_041233_update_table_gov_services_add_cooldown extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('gov_services', 'cooldown', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('gov_services', 'cooldown');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180827_041233_update_table_gov_services_add_cooldown cannot be reverted.\n";

        return false;
    }
    */
}
