<?php

use yii\db\Migration;

/**
 * Class m180710_091720_add_id_service_goal_col_to_service_types
 */
class m180710_091720_add_id_service_goal_col_to_service_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('service_types', 'id_service_goal', $this->integer());

        $this->addForeignKey(
            'fk-service_types-id_service_goal',
            'service_types',
            'id_service_goal',
            'service_goal',
            'id',
            'NO ACTION'
        );

        $this->addCommentOnColumn('service_types', 'id_service_goal', 'Ссылка на цель визита');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-service_types-id_service_goal', 'service_types');
        $this->dropColumn('service_types', 'id_service_goal');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180710_091720_add_id_service_goal_col_to_servicetypes cannot be reverted.\n";

        return false;
    }
    */
}
