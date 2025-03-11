<?php

use yii\db\Migration;

/**
 * Class m180730_070952_del_id_service_goal_column_from_service_types
 */
class m180730_070952_del_id_service_goal_column_from_service_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('service_types', 'id_service_goal');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180730_070952_del_id_service_goal_column_from_service_types cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180730_070952_del_id_service_goal_column_from_service_types cannot be reverted.\n";

        return false;
    }
    */
}
