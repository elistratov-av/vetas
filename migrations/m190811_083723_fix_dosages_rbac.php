<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_083723_fix_dosages__rbac
 */
class m190811_083723_fix_dosages_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('auth_item', ['name' => 'data.dosages.manage'], ['name' => 'data.dosages.mange']);
        $this->update('auth_item', ['name' => 'data.dosages.manage.W'], ['name' => 'data.dosages.mange.W']);

        $this->update('auth_item_child', ['child' => 'data.dosages.manage'], ['child' => 'data.dosages.mange']);
        $this->update('auth_item_child', ['child' => 'data.dosages.manage.W'], ['child' => 'data.dosages.mange.W']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190811_083723_fix_dosages__rbac cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190811_083723_fix_dosages__rbac cannot be reverted.\n";

        return false;
    }
    */
}
