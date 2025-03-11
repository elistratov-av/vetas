<?php

use app\commands\migrate\Migration;

/**
 * Class m211018_094935_3266_update_dead_pets
 */
class m211018_094935_3266_update_dead_pets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        /*
         * Животные, что выбыли из приюта по причине падежа, не снимались с учета
         * Метод v2/shelter/pets/departure поправили в коммите 2c73ae66a8cdeb5d7a068ceb841214a321f66f19
         * Теперь снимем с учета животных, которые выбили до внесения исправления
         */
        $sql = <<<SQL
UPDATE pets
SET
    id_reg_expire_reason = sub_q.id_reg_expire_reason,
    reg_expire_date = sub_q.departure_date
FROM (SELECT
         shelter_guests.departure_date,
         shelter_guests.id_pet,
          (SELECT id FROM reg_expire_reasons WHERE tech_name = 'DEATH') AS id_reg_expire_reason
FROM
    pets
INNER JOIN shelter_guests on pets.id = shelter_guests.id_pet
WHERE shelter_guests.departure_reason = 'DEATH'
AND pets.id_reg_expire_reason IS NULL) as sub_q
WHERE sub_q.id_pet = pets.id
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211018_094935_3266_update_dead_pets cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211018_094935_3266_update_dead_pets cannot be reverted.\n";

        return false;
    }
    */
}
