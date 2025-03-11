<?php

use app\commands\migrate\Migration;

/**
 * Class m190524_154144_extend_pets_and_pet_owners
 */
class m190524_154144_extend_pets_and_pet_owners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * pet_owners
         */
        $this->addColumn(
            'pet_owners',
            'entrepreneur',
            $this->boolean()->notNull()->defaultValue('false')
        );

        $this->addCommentOnColumn(
            'pet_owners',
            'entrepreneur',
            'Флаг: индивидуальный предприниматель'
        );

        /*
         * pets
         */
        $this->addColumn(
            'pets',
            'date_plan_rabies_vaccination',
            $this->date()
        );

        $this->addCommentOnColumn(
            'pets',
            'date_plan_rabies_vaccination',
            'Дата плановой вакцинации, указанной вручную'
        );

        $this->addColumn(
            'pets',
            'date_plan_identification',
            $this->date()
        );

        $this->addCommentOnColumn(
            'pets',
            'date_plan_identification',
            'Дата плановой идентификации, указанной вручную'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'pet_owners',
            'entrepreneur'
        );

        $this->dropColumn(
            'pets',
            'date_plan_rabies_vaccination'
        );

        $this->dropColumn(
            'pets',
            'date_plan_identification'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190524_154144_extend_pets_and_pet_owners cannot be reverted.\n";

        return false;
    }
    */
}
