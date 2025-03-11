<?php

use app\commands\migrate\Migration;

/**
 * Class m190423_122715_add_expiration_date_to_balances
 */
class m190423_122715_add_expiration_date_to_balances extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'balance_drugs',
            'expiration_date',
            $this->date()
        );

        $this->addCommentOnColumn(
            'balance_drugs',
            'expiration_date',
            'Годен до'
        );


        $this->addColumn(
            'balance_exp_materials',
            'expiration_date',
            $this->date()
        );

        $this->addCommentOnColumn(
            'balance_exp_materials',
            'expiration_date',
            'Годен до'
        );


        $this->addColumn(
            'balance_vaccines',
            'expiration_date',
            $this->date()
        );

        $this->addCommentOnColumn(
            'balance_vaccines',
            'expiration_date',
            'Годен до'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'balance_drugs',
            'expiration_date'
        );

        $this->dropColumn(
            'balance_exp_materials',
            'expiration_date'
        );

        $this->dropColumn(
            'balance_vaccines',
            'expiration_date'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190423_122715_add_expiration_date_to_balances cannot be reverted.\n";

        return false;
    }
    */
}
