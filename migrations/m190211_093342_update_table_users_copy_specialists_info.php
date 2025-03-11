<?php

use app\commands\migrate\Migration;

/**
 * Class m190211_093342_update_table_users_copy_specialists_info
 */
class m190211_093342_update_table_users_copy_specialists_info extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
update users 
set f_fio = s.f_fio, 
  i_fio = s.i_fio, 
  o_fio = s.o_fio, 
  birthday = s.birthday, 
  sex = s.sex, 
  photo = s.photo
from specialists s 
where users.id = s.id_user
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
update users u 
set f_fio = null, 
  i_fio = null, 
  o_fio = null, 
  birthday = null, 
  sex = null, 
  photo = null
SQL;

        $this->execute($sql);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190211_093342_update_table_users_copy_specialists_info cannot be reverted.\n";

        return false;
    }
    */
}
