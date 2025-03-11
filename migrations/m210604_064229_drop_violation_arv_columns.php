<?php

use app\commands\migrate\Migration;

/**
 * Class m210604_064229_drop_violation_arv_columns
 */
class m210604_064229_drop_violation_arv_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('violation_ARV', 'sum');
        $this->dropColumn('violation_ARV', 'decree_number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('violation_ARV', 'sum', $this->integer()->notNull()->defaultValue(0)->comment('Сумма штрафа'));
        $this->addColumn('violation_ARV', 'decree_number', $this->string()->unique()->comment('Номер постановления по АПН'));
    }
}
