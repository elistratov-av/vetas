<?php

use app\commands\migrate\Migration;

/**
 * Class m190221_143214_visits_gov_services_col_apply_discount
 */
class m190221_143214_visits_gov_services_col_apply_discount extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'visits_gov_services',
            'apply_discount',
            $this->boolean()->notNull()->defaultValue('false')
        );
        $this->addCommentOnColumn(
            'visits_gov_services',
            'apply_discount',
            'Применена скидка'
        );

        $this->addColumn(
            'visits_gov_services',
            'apply_night_discount',
            $this->boolean()->notNull()->defaultValue('false')
        );
        $this->addCommentOnColumn(
            'visits_gov_services',
            'apply_night_discount',
            'Применен ночной тариф'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'visits_gov_services',
            'apply_discount'
        );

        $this->dropColumn(
            'visits_gov_services',
            'apply_night_discount'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190221_143214_visits_gov_services_col_apply_discount cannot be reverted.\n";

        return false;
    }
    */
}
