<?php

use app\commands\migrate\Migration;

/**
 * Class m190606_102204_add_date_plan_lept_vaccination_to_pets
 */
class m190606_102204_add_date_plan_lept_vaccination_to_pets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
        * pets
        */
        $this->addColumn(
            'pets',
            'date_plan_lept_vaccination',
            $this->date()
        );

        $this->addCommentOnColumn(
            'pets',
            'date_plan_lept_vaccination',
            'Дата плановой вакцинации от лептоспироза (актуально для собак)'
        );

        $this->addCommentOnColumn(
            'pets',
            'date_plan_rabies_vaccination',
            'Дата плановой вакцинации от бешенства'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'pets',
            'date_plan_lept_vaccination'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190606_102204_add_date_plan_lept_vaccination_to_pets cannot be reverted.\n";

        return false;
    }
    */
}
