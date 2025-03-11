<?php

use yii\db\Migration;

/**
 * Class m180713_152410_create_pet_owners_contact_types_view
 */
class m180713_152410_create_pet_owners_contact_types_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
$sql = <<<SQL
CREATE OR REPLACE VIEW public.pet_owners_contact_types AS 
 SELECT contacts.entity_id,
    contacts.id_contact_type
   FROM contacts
  WHERE contacts.entity_type::text = 'jur_person'::text;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.pet_owners_contact_types");
    }

}
