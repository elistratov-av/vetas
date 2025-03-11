<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_144926_alt_add_columns_to_organizations_table
 */
class m190811_144926_alt_add_columns_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'comment', $this->string(1000));
        $this->addColumn('organizations', 'clarification_schedule', $this->string(3000));
        $this->addColumn('organizations', 'capital_structure', $this->boolean()->defaultValue(false));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'comment');
        $this->dropColumn('organizations', 'clarification_schedule');
        $this->dropColumn('organizations', 'capital_structure');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190811_144926_alt_add_columns_to_organizations_table cannot be reverted.\n";

        return false;
    }
    */
}
