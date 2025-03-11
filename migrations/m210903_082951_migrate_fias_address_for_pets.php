<?php

use app\commands\migrate\Migration;

/**
 * Class m210903_082951_migrate_fias_address_for_pets
 */
class m210903_082951_migrate_fias_address_for_pets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->getPets() as $pet) {
            $fiasAddressAttr = $this->findFiasAddress($pet['id_fias_address']);
            if (!$fiasAddressAttr) {
                continue;
            }
            unset($fiasAddressAttr['id']);
            $id = $this->insertFiasAddress($fiasAddressAttr);
            $this->update('pets', ['id_fias_address' => $id], 'id =' . $pet['id']);
        }
    }

    private function findFiasAddress($id)
    {
        return Yii::$app->db->createCommand("
            SELECT *
            FROM fias_addresses
            WHERE id = $id
        ")->queryOne();
    }

    private function insertFiasAddress($attributes)
    {
        $fields = implode(',', array_keys($attributes));
        $values = implode(',',
            array_map(
                function ($value, $key) {
                    if ($value === null) {
                        return 'null';
                    }
                    if (is_integer($value)) {
                        return $value;
                    } else {
                        return "'" . $value . "'";
                    }
                }, array_values($attributes), array_keys($attributes))
        );

        return Yii::$app->db->createCommand("
            INSERT INTO fias_addresses
            ($fields) values ($values)
            RETURNING id;
        
        ")->queryScalar();

    }

    /**
     * @return \app\models\db\PetOwners[]
     */
    private function getPets()
    {
        return Yii::$app->db->createCommand("
            SELECT pets.id, pets.id_fias_address, pets.is_address_pet_owners
            FROM pets
                INNER JOIN pets_to_owner ON pets_to_owner.id_pet = pets.id
                INNER JOIN pet_owners ON pet_owners.id = pets_to_owner.id_owner
            WHERE pets.id_fias_address is not null
        ")->queryAll();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210903_082951_migrate_fias_address_for_pets cannot be reverted.\n";

        return true;
    }
}
