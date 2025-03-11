<?php

use app\commands\migrate\Migration;

/**
 * Class m190529_130718_fix_violation_history
 */
class m190529_130718_fix_violation_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-violation_history-id_inspector',
            'violation_history',
            'id_inspector',
            'users',
            'id',
            'NO ACTION',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-violation_history-id_inspector',
            'violation_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190529_130718_fix_violation_history cannot be reverted.\n";

        return false;
    }
    */
}
