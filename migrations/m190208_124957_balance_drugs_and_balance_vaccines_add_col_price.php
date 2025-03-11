<?php

use app\commands\migrate\Migration;

/**
 * Class m190208_124957_balance_drugs_and_balance_vaccines_add_col_price
 */
class m190208_124957_balance_drugs_and_balance_vaccines_add_col_price extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // balance_drugs
        $this->addColumn(
            'balance_drugs',
            'price',
            $this->decimal(8, 2)
        );
        $this->execute('ALTER TABLE balance_drugs ADD CONSTRAINT check_price CHECK (price >= 0)');
        $this->addCommentOnColumn(
            'balance_drugs',
            'price',
            'Цена'
        );

        // balance_vaccines
        $this->addColumn(
            'balance_vaccines',
            'price',
            $this->decimal(8, 2)
        );
        $this->execute('ALTER TABLE balance_vaccines ADD CONSTRAINT check_price CHECK (price >= 0)');
        $this->addCommentOnColumn(
            'balance_vaccines',
            'price',
            'Цена'
        );

        $this->update('balance_drugs',['price' => 0]);
        $this->update('balance_vaccines',['price' => 0]);

        $this->execute('ALTER TABLE balance_drugs ALTER COLUMN price SET NOT NULL');
        $this->execute('ALTER TABLE balance_vaccines ALTER COLUMN price SET NOT NULL');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'balance_drugs',
            'price'
        );

        $this->dropColumn(
            'balance_vaccines',
            'price'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190208_124957_balance_drugs_and_balance_vaccines_add_col_price cannot be reverted.\n";

        return false;
    }
    */
}
