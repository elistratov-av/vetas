<?php

use yii\db\Migration;

/**
 * Class m180817_103936_drop_view_pet_owners
 */
class m180817_103936_drop_view_pet_owners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP VIEW IF EXISTS pet_owners');
        $this->renameTable('pet_owners_src', 'pet_owners');
        $this->addColumn('pet_owners', 'fullname', $this->string());
        $this->execute("UPDATE pet_owners SET fullname = ((f_fio || ' ' || i_fio || ' ' || o_fio))");
        
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION pet_owner_fullname_func()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.fullname = ((NEW.f_fio || ' ' || NEW.i_fio || ' ' || NEW.o_fio));

    RETURN NEW;
END;
$$;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER pet_owners_fullname_trg
BEFORE INSERT OR UPDATE
ON pet_owners
FOR EACH ROW
EXECUTE PROCEDURE pet_owner_fullname_func();
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180817_103936_drop_view_pet_owners cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180817_103936_drop_view_pet_owners cannot be reverted.\n";

        return false;
    }
    */
}
