<?php

use app\commands\migrate\Migration;

/**
 * Class m230922_053310_add_payment_link_to_visits_table
 */
class m230922_053310_add_payment_link_to_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'payment_link', $this->string()->comment('Ссылка на оплату клиенту для услуг реализующих оплату через ЕПШ'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('visits', 'payment_link');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230922_053310_add_payment_link_to_visits_table cannot be reverted.\n";

        return false;
    }
    */
}
