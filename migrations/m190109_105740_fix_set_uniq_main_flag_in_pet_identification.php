<?php

use app\commands\migrate\Migration;

/**
 * Class m190109_105740_fix_set_uniq_main_flag_in_pet_identification
 */
class m190109_105740_fix_set_uniq_main_flag_in_pet_identification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP TRIGGER 
            trigger_set_uniq_main_flag_in_pet_identification
            ON pet_identification
        ');
        $this->execute('DROP FUNCTION
            set_uniq_main_flag_in_pet_identification
        ');

        $sql =<<<SQL
CREATE FUNCTION set_uniq_main_flag_in_pet_identification()
RETURNS trigger AS \$BODY\$

BEGIN
IF NEW.main_flag = TRUE THEN
	UPDATE pet_identification SET main_flag = FALSE WHERE id_pet = NEW.id_pet AND new.id <> id;
END IF;
	RETURN NEW;
END;

\$BODY\$
LANGUAGE 'plpgsql';
SQL;

        $this->execute($sql);
        $this->execute("
            CREATE TRIGGER 
                trigger_set_uniq_main_flag_in_pet_identification 
            AFTER INSERT OR UPDATE ON pet_identification 
            FOR EACH ROW EXECUTE PROCEDURE 
                set_uniq_main_flag_in_pet_identification();"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER 
            trigger_set_uniq_main_flag_in_pet_identification
            ON pet_identification
        ');
        $this->execute('DROP FUNCTION
            set_uniq_main_flag_in_pet_identification
        ');

        $sql =<<<SQL
CREATE FUNCTION set_uniq_main_flag_in_pet_identification()
RETURNS trigger AS \$BODY\$

BEGIN
IF NEW.main_flag = TRUE THEN
	UPDATE pet_identification SET main_flag = FALSE WHERE id_pet = NEW.id_pet;
END IF;
	RETURN NEW;
END;

\$BODY\$
LANGUAGE 'plpgsql';
SQL;

        $this->execute($sql);
        $this->execute("
            CREATE TRIGGER 
                trigger_set_uniq_main_flag_in_pet_identification 
            BEFORE INSERT OR UPDATE ON pet_identification 
            FOR EACH ROW EXECUTE PROCEDURE 
                set_uniq_main_flag_in_pet_identification();"
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190109_105740_fix_set_uniq_main_flag_in_pet_identification cannot be reverted.\n";

        return false;
    }
    */
}
