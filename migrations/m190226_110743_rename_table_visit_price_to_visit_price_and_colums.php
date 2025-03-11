<?php

use app\commands\migrate\Migration;

/**
 * Class m190226_110743_rename_table_visit_price_to_visit_price_and_colums
 */
class m190226_110743_rename_table_visit_price_to_visit_price_and_colums extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('visit_discount', 'visit_price');

        $columns = [
            'price' => 'Общая сумма без скидок',
            'price_with_discount' => 'Общая сумма со скидками (результат)',
            'balance_tmc_total' => 'Сумма за ТМЦ',
            'balance_tmc_discount' => 'Скидка за ТМЦ',
            'service_total' => 'Сумма за услуги',
            'service_discount' => 'Скидка за услуги',
        ];

        foreach ($columns as $column => $desc){
            $this->addColumn(
                'visit_price',
                $column,
                $this->decimal(8, 2)
            );

            $this->addCommentOnColumn(
                'visit_price',
                $column,
                $desc
            );
        }

        $this->addColumn(
            'visit_price',
            'bill_num', // !!!!!!!!!!!!!!!!! уточнить
            $this->text(255)
        );
        $this->addCommentOnColumn(
            'visit_price',
            'bill_num',
            'Номер квитации'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $columns = [
            'price' => 'Общая сумма без скидок',
            'price_with_discount' => 'Общая сумма со скидками (результат)',
            'balance_tmc_total' => 'Сумма за ТМЦ',
            'balance_tmc_discount' => 'Скидка за ТМЦ',
            'service_total' => 'Сумма за услуги',
            'service_discount' => 'Скидка за услуги',
            'bill_num' => '',
        ];

        foreach ($columns as $column => $desc){
            $this->dropColumn('visit_price', $column);
        }

        $this->renameTable('visit_price', 'visit_discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190226_110743_rename_table_visit_price_to_visit_price_and_colums cannot be reverted.\n";

        return false;
    }
    */
}
