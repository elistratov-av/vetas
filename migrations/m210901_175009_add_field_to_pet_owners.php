<?php

use app\commands\migrate\Migration;

/**
 * Class m210901_175009_add_field_to_pet_owners
 */
class m210901_175009_add_field_to_pet_owners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'pet_owners',
            'addresses_is_equal',
            $this->boolean()->comment('Флаг: Адрес регистрации и фактический адрес совпадают')
        );

        $this->updateFieldValues();
    }

    private function updateFieldValues()
    {
        Yii::$app->db->createCommand('
            UPDATE pet_owners
            SET addresses_is_equal = (
                SELECT CASE
                           WHEN xcount = 0 THEN true
                           WHEN xcount = 1 THEN true
                           else false
                           END as result
                FROM (
                         SELECT count(*) as xcount
                         FROM (
                                  SELECT regionguid,
                                         cityguid,
                                         streetguid,
                                         houseguid
                                  FROM fias_addresses
                                  WHERE pet_owners.id_fias_address is not null
                                    AND id = pet_owners.id_fias_address
                                  union
                                  SELECT regionguid,
                                         cityguid,
                                         streetguid,
                                         houseguid
                                  FROM fias_addresses
                                  WHERE pet_owners.id_fact_fias_address is not null
                                    AND id = pet_owners.id_fact_fias_address
                              ) as data
                     ) as data
            )
        ')->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owners', 'addresses_is_equal');
    }
}
