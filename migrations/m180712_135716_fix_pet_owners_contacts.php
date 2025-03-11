<?php

use yii\db\Migration;

/**
 * Class m180712_135716_fix_pet_owners_contacts
 */
class m180712_135716_fix_pet_owners_contacts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE contacts SET entity_type = 'pet_owner' WHERE entity_type IN ('nat_person', 'jur_person')");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180712_135716_fix_pet_owners_contacts cannot be reverted.\n";

        return false;
    }
}
