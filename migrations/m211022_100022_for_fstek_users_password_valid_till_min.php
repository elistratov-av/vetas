<?php

use app\commands\migrate\Migration;

/**
 * Class m211022_100022_for_fstek_users_password_valid_till_min
 */
class m211022_100022_for_fstek_users_password_valid_till_min extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.users', 'password_valid_till_min', $this->dateTime(0)
            ->comment('Минимальный срок действия пароля'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.users', 'password_valid_till_min');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211022_100022_for_fstek_users_password_valid_till_min cannot be reverted.\n";

        return false;
    }
    */
}
