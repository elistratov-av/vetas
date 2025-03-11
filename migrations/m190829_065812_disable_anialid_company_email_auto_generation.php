<?php

use app\commands\migrate\Migration;

/**
 * Class m190829_065812_disable_anialid_company_email_auto_generation
 */
class m190829_065812_disable_anialid_company_email_auto_generation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('drop view if exists animalid.view_pets');
        $this->execute('drop view if exists animalid.view_companys');

        $sql1 = <<<SQL
create or replace view animalid.view_companys as
  with co as
  (
      select co.*, ct.type
      from contacts co , contact_types ct
      where  co.id_contact_type = ct.id
             and (ct.type = 'phone' or ct.type = 'email')
             and ct.entity_type = 'organization'
             and co.entity_type = 'organization'
             and co.main_flag = true
  )
  select po.id, po.name,
    co.name as email,
    com.name as phone,
    fa.full_address as address,
    po.created_at AS createdate,
    po.updated_at AS upddate
  from organizations po
    join co on po.id = co.entity_id and co.type = 'email'
    left outer join co as com on po.id = com.entity_id and com.type = 'phone'
    left outer join fias_addresses fa on po.id_fias_address = fa.id
SQL;
        $this->execute($sql1);

        $sql2 = <<<SQL
create view animalid.view_pets as
  SELECT
    pt.id,
    pi.identification_code                       AS chip,
    pi.created_at                                AS chipdate,
    pt.id_species                                AS kindid,
    pt.id_breed                                  AS breedid,
    sp.name                                      AS kind,
    bd.name                                      AS breed,
    CASE
    WHEN ((pt.sex) :: text = 'm' :: text)
      THEN 'самец' :: text
    WHEN ((pt.sex) :: text = 'f' :: text)
      THEN 'самка' :: text
    ELSE NULL :: text
    END                                          AS sex,
    (pt.birthday) :: timestamp without time zone AS birthday,
    pt.created_at                                AS createdate,
    pt.updated_at                                AS upddate,
    pt.id_reg_organization                       AS companyid
  FROM pets pt,
    pet_identification pi,
    animalid.view_companys co,
    breeds bd,
    species sp
  WHERE ((pi.id_pet = pt.id) AND (pi.main_flag = true) AND (pi.id_ident_type = 1) AND (pt.sex IS NOT NULL) AND
         (pt.birthday IS NOT NULL) AND (pt.id_reg_organization IS NOT NULL) AND (pt.id_reg_organization = co.id) AND
         (pt.id_breed = bd.id) AND (pt.id_species = sp.id))

SQL;

        $this->execute($sql2);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190829_065812_disable_anialid_company_email_auto_generation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190829_065812_disable_anialid_company_email_auto_generation cannot be reverted.\n";

        return false;
    }
    */
}
