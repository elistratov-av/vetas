<?php

use yii\db\Migration;

/**
 * Class m180806_124341_specialist_FIO_field_trigger
 */
class m180806_124341_specialist_FIO_field_trigger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('specialists', 'fullname', $this->string());
        $this->execute("UPDATE specialists SET fullname = ((f_fio || ' ' || i_fio || ' ' || o_fio))");

$sql = <<<SQL
CREATE OR REPLACE FUNCTION specialist_fullname_func()
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
CREATE TRIGGER specialist_fullname_trg
BEFORE INSERT OR UPDATE
ON specialists
FOR EACH ROW
EXECUTE PROCEDURE specialist_fullname_func();
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
DROP TRIGGER specialist_fullname_trg; 
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
        echo "m180806_124341_specialist_FIO_field_trigger cannot be reverted.\n";

        return false;
    }
    */
}
