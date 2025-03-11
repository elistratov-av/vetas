<?php

use app\commands\migrate\Migration;
use yii\db\Expression;

/**
 * Class m210412_061224_add_field_to_balance_table
 */
class m210412_061224_add_field_to_balance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tmc.balance', 'production_date', $this->date()->comment('Дата изготовления'));
        $this->update('tmc.balance', ['production_date' => new Expression('(SELECT created_at FROM tmc.balance t1 WHERE t1.id = balance.id)')]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tmc.balance', 'production_date');
    }

}
