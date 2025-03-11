<?php

use yii\db\Migration;

/**
 * Class m180725_102658_update_norm_pet_owners_view
 */
class m180725_102658_update_norm_pet_owners_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW "public"."pet_owners" AS 
 SELECT pet_owners_src.id,
 pet_owners_src.f_fio,
 pet_owners_src.i_fio,
 pet_owners_src.o_fio,
 pet_owners_src.jur_name,
 pet_owners_src.inn,
 pet_owners_src.ogrn,
 pet_owners_src.birthday,
 pet_owners_src.snils,
 pet_owners_src.id_address,
 pet_owners_src.created_by,
 pet_owners_src.updated_by,
 pet_owners_src.created_at,
 pet_owners_src.updated_at,
 pet_owners_src.id_fact_address,
 concat_ws(' '::text, pet_owners_src.f_fio, pet_owners_src.i_fio, pet_owners_src.o_fio) AS fullname,
 addresses.id_area,
 addresses.id_district
 FROM pet_owners_src
 LEFT OUTER JOIN addresses ON pet_owners_src.id_address = addresses.id;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW "public"."pet_owners" AS 
 SELECT pet_owners_src.id,
 pet_owners_src.f_fio,
 pet_owners_src.i_fio,
 pet_owners_src.o_fio,
 pet_owners_src.jur_name,
 pet_owners_src.inn,
 pet_owners_src.ogrn,
 pet_owners_src.birthday,
 pet_owners_src.snils,
 pet_owners_src.id_address,
 pet_owners_src.created_by,
 pet_owners_src.updated_by,
 pet_owners_src.created_at,
 pet_owners_src.updated_at,
 pet_owners_src.id_fact_address,
 concat_ws(' '::text, pet_owners_src.f_fio, pet_owners_src.i_fio, pet_owners_src.o_fio) AS fullname
 FROM pet_owners_src;
SQL;

        $this->execute($sql);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180725_102658_update_norm_pet_owners_view cannot be reverted.\n";

        return false;
    }
    */
}
