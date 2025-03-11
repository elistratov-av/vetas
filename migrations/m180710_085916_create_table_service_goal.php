<?php

use yii\db\Migration;

/**
 * Class m180710_085916_create_table_service_goal
 */
class m180710_085916_create_table_service_goal extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('service_goal',[
            'id' => $this->primaryKey()->comment('id'),
            'name' => $this->string(100)->notNull()->comment('Наименование'),
            'sort_by' => $this->integer()->comment('Сортировка')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180710_085916_create_table_service_goal cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180710_085916_create_table_service_goal cannot be reverted.\n";

        return false;
    }
    */
}
