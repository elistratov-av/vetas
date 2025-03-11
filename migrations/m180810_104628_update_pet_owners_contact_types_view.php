<?php

use yii\db\Migration;

/**
 * Class m180810_104628_update_pet_owners_contact_types_view
 */
class m180810_104628_update_pet_owners_contact_types_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP VIEW pet_owners_contact_types');
        
        $sql = <<<SQL
        CREATE OR REPLACE VIEW "pet_owners_contact_types" AS 
        SELECT contacts.entity_id,
        contacts.id_contact_type
        FROM contacts
        WHERE contacts.entity_type::text = 'pet_owner'::text;
SQL;
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180810_104628_update_pet_owners_contact_types_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180810_104628_update_pet_owners_contact_types_view cannot be reverted.\n";

        return false;
    }
    */
}
