<?php

use app\commands\migrate\Migration;

/**
 * Class m180829_105118_update_specialists_fullname_function
 */
class m180829_105118_update_specialists_fullname_function extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE specialists SET fullname = ((f_fio || ' ' || i_fio || COALESCE(' ' || o_fio, '')))");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION specialist_fullname_func()
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
DROP FUNCTION specialist_fullname_func;
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
        echo "m180829_105118_update_specialists_fullname_function cannot be reverted.\n";

        return false;
    }
    */
}
