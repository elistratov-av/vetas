<?php

use app\commands\migrate\Migration;

/**
 * Class m210824_052007_add_quarantine_id_column_to_violation
 */
class m210824_052007_add_quarantine_id_column_to_violation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('violation', 'id_quarantine', $this->integer()->comment('Карантин в рамках которого выписано нарушение'));

        $this->addForeignKey(
            'fk-violation_id_quarantine',
            'violation',
            'id_quarantine',
            'quarantines',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('violation', 'id_quarantine');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210824_052007_add_quarantine_id_column_to_violation cannot be reverted.\n";

        return false;
    }
    */
}
