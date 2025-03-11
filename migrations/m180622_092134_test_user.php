<?php

use yii\db\Migration;

/**
 * Class m180622_092134_test_user
 */
class m180622_092134_test_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $hash =  \Yii::$app->getSecurity()->generatePasswordHash('qwer123');

        $this->insert('users', ['login' => 'testuser', 'password' => $hash]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180622_092134_test_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180622_092134_test_user cannot be reverted.\n";

        return false;
    }
    */
}
