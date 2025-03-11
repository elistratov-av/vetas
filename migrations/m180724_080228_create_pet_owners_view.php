<?php

use yii\db\Migration;

/**
 * Class m180724_080228_create_norm_pet_owners_view
 */
class m180724_080228_create_pet_owners_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE  IF EXISTS  pet_owners RENAME TO pet_owners_src");
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.pet_owners");
        $this->execute("ALTER TABLE pet_owners_src RENAME TO pet_owners");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_080228_create_norm_pet_owners_view cannot be reverted.\n";

        return false;
    }
    */
}
