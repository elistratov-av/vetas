<?php

use yii\db\Migration;

/**
 * Class m180731_123058_update_pet_owners_view
 */
class m180731_123058_update_pet_owners_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW "pet_owners" AS 
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
 addresses.id_district,
 pet_owners_src.is_legal
 FROM pet_owners_src
 LEFT OUTER JOIN addresses ON pet_owners_src.id_address = addresses.id;
SQL;
        $this->execute($sql);
        
        $sql = <<<SQL
CREATE OR REPLACE RULE "pet_owners_ins" AS
    ON INSERT TO pet_owners
    DO INSTEAD
INSERT INTO pet_owners_src (
    f_fio, 
    i_fio, 
    o_fio, 
    jur_name, 
    inn, 
    ogrn, 
    birthday, 
    snils, 
    id_address, 
    created_by, 
    updated_by, 
    created_at, 
    updated_at, 
    id_fact_address, 
    is_legal
)
VALUES(
    NEW.f_fio, 
    NEW.i_fio, 
    NEW.o_fio, 
    NEW.jur_name, 
    NEW.inn, 
    NEW.ogrn, 
    NEW.birthday, 
    NEW.snils, 
    NEW.id_address, 
    NEW.created_by, 
    NEW.updated_by, 
    NEW.created_at, 
    NEW.updated_at, 
    NEW.id_fact_address, 
    NEW.is_legal
);
SQL;
        $this->execute($sql);
        
        $sql = <<<SQL
CREATE OR REPLACE RULE "pet_owners_upd" AS
    ON UPDATE TO pet_owners
    DO INSTEAD
UPDATE pet_owners_src SET (
    f_fio, 
    i_fio, 
    o_fio, 
    jur_name, 
    inn, 
    ogrn, 
    birthday, 
    snils, 
    id_address, 
    created_by, 
    updated_by, 
    created_at, 
    updated_at, 
    id_fact_address, 
    is_legal
) =
(
    NEW.f_fio, 
    NEW.i_fio, 
    NEW.o_fio, 
    NEW.jur_name, 
    NEW.inn, 
    NEW.ogrn, 
    NEW.birthday, 
    NEW.snils, 
    NEW.id_address, 
    NEW.created_by, 
    NEW.updated_by, 
    NEW.created_at, 
    NEW.updated_at, 
    NEW.id_fact_address, 
    NEW.is_legal
)
WHERE pet_owners_src.id = NEW.id;
SQL;
        $this->execute($sql);
        
        $sql = <<<SQL
CREATE OR REPLACE RULE "pet_owners_del" AS
    ON DELETE TO pet_owners
    DO INSTEAD
    DELETE FROM pet_owners_src WHERE id = OLD.id;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP VIEW pet_owners');
        $sql = <<<SQL
CREATE OR REPLACE VIEW "pet_owners" AS 
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
        
        $this->execute('DROP RULE IF EXISTS pet_owners_ins ON pet_owner');
        $this->execute('DROP RULE IF EXISTS pet_owners_upd ON pet_owner');
        $this->execute('DROP RULE IF EXISTS pet_owners_del ON pet_owner');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180731_123058_update_pet_owners_view cannot be reverted.\n";

        return false;
    }
    */
}
