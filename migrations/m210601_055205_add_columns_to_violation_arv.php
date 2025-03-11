<?php

use app\commands\migrate\Migration;

/**
 * Class m210601_055205_add_columns_to_violation_arv
 */
class m210601_055205_add_columns_to_violation_arv extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('violation_ARV', 'sum', $this->integer()->notNull()->defaultValue(0)->comment('Сумма штрафа'));
        $this->addColumn('violation_ARV', 'decree_number', $this->string()->unique()->comment('Номер постановления по АПН'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('violation_ARV', 'sum');
        $this->dropColumn('violation_ARV', 'decree_number');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210601_055205_add_columns_to_violation_arv cannot be reverted.\n";

        return false;
    }
    */
}
