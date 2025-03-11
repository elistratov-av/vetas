<?php

use app\commands\migrate\Migration;

/**
 * Class m210216_111019_add_contacts_field
 */
class m210216_111019_add_contacts_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.pet_owners_link_history', 'contacts', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.pet_owners_link_history', 'contacts');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210216_111019_add_contacts_field cannot be reverted.\n";

        return false;
    }
    */
}
