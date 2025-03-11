<?php

use app\commands\migrate\Migration;

/**
 * Class m190911_114022_edit_organizations_capital_structure
 */
class m190911_114022_edit_organizations_capital_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organizations', 'capital_structure');
        $this->addColumn('organizations', 'capital_structure', $this->boolean()->defaultValue(true));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190911_114022_edit_organizations_capital_structure cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190911_114022_edit_organizations_capital_structure cannot be reverted.\n";

        return false;
    }
    */
}
