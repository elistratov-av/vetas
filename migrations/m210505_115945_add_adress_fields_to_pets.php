<?php

use app\commands\migrate\Migration;

/**
 * Class m210505_115945_add_adress_fields_to_pets
 */
class m210505_115945_add_adress_fields_to_pets extends Migration
{
    const FK_PETS_FIAS_ADDRESSES_ID = 'fk-pets-fias_addresses_id';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pets', 'id_fias_address', $this->integer()->comment('Адрес содержания животного'));
        $this->addColumn('pets', 'is_address_pet_owners', $this->boolean()->comment('Адрес содержания совпадает с адресом владельца'));

        $this->addForeignKey(self::FK_PETS_FIAS_ADDRESSES_ID,
            'pets',
            'id_fias_address',
            'fias_addresses',
            'id',
            'NO ACTION',
            'NO ACTION');

        $sql = <<<SQL
WITH po as (SELECT id_fias_address, id_fact_fias_address, pto.id_pet, po.id
FROM pets_to_owner pto
LEFT JOIN pet_owners po ON pto.id_owner = po.id
LEFT JOIN pet_owner_type pot ON pto.id_owner_type = pot.id
WHERE (po.is_main IS NULL OR po.is_main = true) AND pot.is_owner = true)

UPDATE public.pets
SET id_fias_address = CASE WHEN po.id_fact_fias_address IS NULL then po.id_fias_address else po.id_fact_fias_address end,
	is_address_pet_owners = CASE WHEN po.id_fact_fias_address IS NULL then false else true end
FROM po
WHERE po.id_pet = pets.id
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_PETS_FIAS_ADDRESSES_ID,'pets');
        $this->dropColumn('pets','id_fias_address');
        $this->dropColumn('pets','is_address_pet_owners');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210505_115945_add_adress_fields_to_pets cannot be reverted.\n";

        return false;
    }
    */
}
