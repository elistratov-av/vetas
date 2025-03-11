<?php

use app\commands\migrate\Migration;

/**
 * Class m190207_091333_update_phone_contacts_from_mosru
 */
class m190207_091333_update_phone_contacts_from_mosru extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
update contacts
set name = regexp_replace(name, '\+', '+7') 
where 
  id_contact_type = 1 
  and entity_id in (select v.id_owner from visits as v join etp.message as m on m.visit_id = v.id) 
  and name ~ '^\+(\d){10}$';
SQL;
        $this->db->createCommand($sql)->execute();

        $sql = <<<SQL
update etp.message
set phone = regexp_replace(phone, '\+', '+7') 
where 
  phone ~ '^\+(\d){10}$';
SQL;
        $this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
