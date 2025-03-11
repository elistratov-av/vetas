<?php

use yii\db\Migration;

/**
 * Class m180713_152955_create_organizations_contact_types_view
 */
class m180713_152955_create_organizations_contact_types_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.organizations_contact_types AS 
 SELECT contacts.entity_id,
    contacts.id_contact_type
   FROM contacts
  WHERE contacts.entity_type::text = 'organization'::text;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.organizations_contact_types");
    }
}
