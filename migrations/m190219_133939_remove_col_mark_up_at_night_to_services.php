<?php

use app\commands\migrate\Migration;

/**
 * Class m190219_133939_remove_col_mark_up_at_night_to_services
 */
class m190219_133939_remove_col_mark_up_at_night_to_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn(
            'services',
            'mark_up_at_night'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(
            'services',
            'mark_up_at_night',
            $this->boolean()->notNull()->defaultValue('false')
        );

        $this->addCommentOnColumn(
            'services',
            'mark_up_at_night',
            'Флаг: ночью действует наценка'
        );

        /*
         *  Все, кроме:
         *      (0283) Транспортировка животного
         *      (0364) Выезд для оказания ветеринарной помощи на дому
         *      (0365) Выезд ветврача
         */
        $this->update(
            'gov_services',
            ['mark_up_at_night' => true],
            ['NOT IN','cod',['0364', '0365', '0283']]
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190219_133939_remove_col_mark_up_at_night_to_services cannot be reverted.\n";

        return false;
    }
    */
}
