<?php

use app\commands\migrate\Migration;

/**
 * Class m190228_152621_add_cols_to_organization
 */
class m190228_152621_add_cols_to_organization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'organizations',
            'point_utilization',
            $this->boolean()->notNull()->defaultValue('false')
        );
        $this->addCommentOnColumn(
            'organizations',
            'point_utilization',
            'Флаг: является пунктом приема трупов животных'
        );

        $this->addColumn(
            'organizations',
            'point_vaccination',
            $this->boolean()->notNull()->defaultValue('false')
        );
        $this->addCommentOnColumn(
            'organizations',
            'point_vaccination',
            'Флаг: является центром бесплатной вакцинации'
        );

        $this->addColumn(
            'organizations',
            'point_registration',
            $this->boolean()->notNull()->defaultValue('false')
        );
        $this->addCommentOnColumn(
            'organizations',
            'point_registration',
            'Флаг: является пунктом регистрации животных'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'organizations',
            'point_utilization'
        );

        $this->dropColumn(
            'organizations',
            'point_vaccination'
        );

        $this->dropColumn(
            'organizations',
            'point_registration'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190228_152621_add_cols_to_organization cannot be reverted.\n";

        return false;
    }
    */
}
