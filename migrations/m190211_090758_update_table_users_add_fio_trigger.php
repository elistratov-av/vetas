<?php

use app\commands\migrate\Migration;

/**
 * Class m190211_090758_update_table_users_add_fio_trigger
 */
class m190211_090758_update_table_users_add_fio_trigger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION user_fullname_func()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.fullname = ((NEW.f_fio || ' ' || NEW.i_fio || COALESCE(' ' || NEW.o_fio, '')));

    RETURN NEW;
END;
$$;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER user_fullname_trg
BEFORE INSERT OR UPDATE
ON users
FOR EACH ROW
EXECUTE PROCEDURE user_fullname_func();
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
DROP TRIGGER IF EXISTS user_fullname_trg ON users; 
DROP FUNCTION IF EXISTS user_fullname_func;
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
        echo "m190211_090758_update_table_users_add_fio_trigger cannot be reverted.\n";

        return false;
    }
    */
}
