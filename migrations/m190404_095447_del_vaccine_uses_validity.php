<?php

use app\commands\migrate\Migration;

/**
 * Class m190404_095447_del_vaccine_uses_validity
 */
class m190404_095447_del_vaccine_uses_validity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('vaccine_uses','validity');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('vaccine_uses','validity', $this->integer()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190404_095447_del_vaccine_uses_validity cannot be reverted.\n";

        return false;
    }
    */
}
