<?php

use app\commands\migrate\Migration;

/**
 * Class m181204_160411_fias_address_add_fullname_trigger
 */
class m181204_160411_fias_address_add_fullname_trigger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql_func = <<<SQL
create or replace function fias_address_full_address_func()
  returns trigger
language plpgsql
as $$
BEGIN
  NEW.full_address = COALESCE(NEW.city, '') ||  COALESCE(', ' || NEW.street, '') || COALESCE(', ' || NEW.house, '') || COALESCE(', ' || NEW.room, '');

  RETURN NEW;
END;
$$;

SQL;
        $this->execute($sql_func);

        $sql_trg = <<<SQL2
create trigger fias_address_full_address_trg
  before insert or update
  on fias_address
  for each row
execute procedure fias_address_full_address_func();
SQL2;

        $this->execute($sql_trg);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
DROP FUNCTION fias_address_full_address_func;
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
        echo "m181204_160411_fias_address_add_fullname_trigger cannot be reverted.\n";

        return false;
    }
    */
}
