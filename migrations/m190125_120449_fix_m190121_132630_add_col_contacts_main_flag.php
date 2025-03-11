<?php

use app\commands\migrate\Migration;

/**
 * Class m190125_120449_fix_m190121_132630_add_col_contacts_main_flag
 */
class m190125_120449_fix_m190121_132630_add_col_contacts_main_flag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP TRIGGER trigger_set_uniq_main_flag_in_contacts ON public.contacts;");
        $this->execute("DROP FUNCTION set_uniq_main_flag_in_contacts();");

        $sql =<<<SQL
CREATE OR REPLACE FUNCTION set_uniq_main_flag_in_contacts()
RETURNS trigger AS \$BODY\$
DECLARE c_type text;
BEGIN


IF NEW.main_flag = TRUE THEN
    -- Выбираем какой тип обновлять
    SELECT  "type" 
    INTO STRICT c_type 
    FROM contact_types 
    WHERE 
          contact_types.id = NEW.id_contact_type
          AND
          contact_types.type IN ('email', 'phone');

    IF FOUND THEN
        -- Проставляем всем котактам этого типа этого entity_id main_flag = false
      	UPDATE contacts SET main_flag = FALSE 
	    WHERE id IN (
              SELECT contacts.id FROM contacts
              LEFT JOIN contact_types ct ON contacts.id_contact_type = ct.id
              WHERE
                entity_id = NEW.entity_id
              AND
                ct.type = c_type
        );
    END IF;
END IF;
	RETURN NEW;
END;
\$BODY\$
LANGUAGE 'plpgsql';
SQL;

        $this->execute($sql);
        $this->execute("
            CREATE TRIGGER 
                trigger_set_uniq_main_flag_in_contacts
            BEFORE INSERT OR UPDATE ON contacts 
            FOR EACH ROW EXECUTE PROCEDURE 
                set_uniq_main_flag_in_contacts();"
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190125_120449_fix_m190121_132630_add_col_contacts_main_flag cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190125_120449_fix_m190121_132630_add_col_contacts_main_flag cannot be reverted.\n";

        return false;
    }
    */
}
