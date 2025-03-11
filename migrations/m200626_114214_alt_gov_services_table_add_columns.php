<?php

use app\commands\migrate\Migration;

/**
 * Class m200626_114214_alt_gov_services_table_add_columns
 */
class m200626_114214_alt_gov_services_table_add_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('gov_services', 'for_broods', $this->string());
        $this->addColumn('gov_services', 'for_multiple', $this->string());
        $this->addColumn('gov_services', 'once_per_day', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('gov_services', 'for_broods');
        $this->dropColumn('gov_services', 'for_multiple');
        $this->dropColumn('gov_services', 'once_per_day');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200626_114214_alt_gov_services_table_add_columns cannot be reverted.\n";

        return false;
    }
    */
}
