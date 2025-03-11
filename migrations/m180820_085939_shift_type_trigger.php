<?php

use yii\db\Migration;

/**
 * Class m180820_085939_shift_type_trigger
 */
class m180820_085939_shift_type_trigger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql =<<<SQL
CREATE FUNCTION shift_type_insert_update_check()
RETURNS trigger AS \$BODY\$

BEGIN
IF new.parent_id IS NULL AND new.color IS NULL THEN
	RAISE EXCEPTION 'Parent types MUST have color value'USING ERRCODE='P0101';
ELSE 
	RETURN NEW;
END IF;
	RETURN NULL;
END;

\$BODY\$
LANGUAGE 'plpgsql';
SQL;

        $this->execute($sql);
        $this->execute("CREATE TRIGGER trigger_shift_type_insert_update_check BEFORE INSERT OR UPDATE ON shift_type 
FOR EACH ROW EXECUTE PROCEDURE shift_type_insert_update_check();");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER trigger_shift_type_insert_update_check ON public.shift_type;');
        $this->execute('DROP FUNCTION public.shift_type_insert_update_check()');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180820_085939_shift_type_trigger cannot be reverted.\n";

        return false;
    }
    */
}
