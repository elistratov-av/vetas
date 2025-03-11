<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_062639_fix_dosages_menu_rbac
 */
class m190811_062639_fix_dosages_menu_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('auth_item', ['name' => 'data.dosages.tab.menu'], ['name' => 'data.doasges.tab.menu']);
        $this->update('auth_item', ['name' => 'data.dosages.add.menu'], ['name' => 'data.doasges.add.menu']);
        $this->update('auth_item', ['name' => 'data.dosages.calculate.menu'], ['name' => 'data.doasges.calculate.menu']);

        $this->update('auth_item_child', ['child' => 'data.dosages.tab.menu'], ['child' => 'data.doasges.tab.menu']);
        $this->update('auth_item_child', ['child' => 'data.dosages.add.menu'], ['child' => 'data.doasges.add.menu']);
        $this->update('auth_item_child', ['child' => 'data.dosages.calculate.menu'], ['child' => 'data.doasges.calculate.menu']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190811_062639_fix_dosages_menu_rbac cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190811_062639_fix_dosages_menu_rbac cannot be reverted.\n";

        return false;
    }
    */
}
