<?php

use app\commands\migrate\Migration;

/**
 * Class m210326_093618_add_id_owner_field_to_pet_owner_history_table
 */
class m210326_093618_add_id_owner_field_to_pet_owner_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners_history', 'id_owner', $this->integer()->defaultValue(null));
        $this->addForeignKey(
            'fk_pet_owners_history_id_owner_history_id_owner',
            'pet_owners_history',
            'id_owner',
            'pet_owners',
            'id',
            'SET NULL'
        );

        $sql = <<<SQL
update pet_owners_history
set id_owner = (
    select distinct on (pto.id_pet)
    max(pto.id_owner)
    from pets_to_owner pto
    where pet_owners_history.id_pet = pto.id_pet
    group by pto.id_pet, pto.id_owner_type
    )
SQL;

        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropColumn('pet_owners_history', 'id_owner');

//        echo "m210326_093618_add_id_owner_field_to_pet_owner_history_table cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210326_093618_add_id_owner_field_to_pet_owner_history_table cannot be reverted.\n";

        return false;
    }
    */
}
