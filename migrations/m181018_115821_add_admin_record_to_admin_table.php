<?php

use app\commands\migrate\Migration;

/**
 * Class m181018_115821_add_admin_record_to_admin_table
 */
class m181018_115821_add_admin_record_to_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $password = Yii::$app->security->generatePasswordHash('c8Fga5XHYRh4rvYK');

        $this->insert('admin', array(
            'login' => 'admin',
            'password' => $password,
        ));


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('admin',[
            'login' => 'admin',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181018_115821_add_admin_record_to_admin_table cannot be reverted.\n";

        return false;
    }
    */
}
