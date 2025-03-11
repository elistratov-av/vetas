<?php

use app\commands\migrate\Migration;

/**
 * Class m190829_065812_disable_anialid_company_email_auto_generation
 */
class m190829_075812_anialid_view_orgs_without_main_flag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('drop view if exists animalid.view_pets');
        $this->execute('drop view if exists animalid.view_companys');

        $sql1 = <<<SQL
create view animalid.view_companys as
with phone as
(
    select ct.entity_id, ct.name, row_number() over (partition by ct.entity_id) as rn
    from contacts ct
      join contact_types c2 on ct.id_contact_type = c2.id and c2.entity_type = 'organization' and type = 'phone'
    where ct.entity_type = 'organization'
),
email as
(
    select ct.entity_id, ct.name, row_number() over (partition by ct.entity_id) as rn
    from contacts ct
     join contact_types c2 on ct.id_contact_type = c2.id and c2.entity_type = 'organization' and type = 'email'
    where ct.entity_type = 'organization'
)
select org.id,
org.name as org_name,
phone.name as phone, 
email.name as email,
fa.full_address as address,
org.created_at AS createdate,
org.updated_at AS upddate
from  organizations org
join email on email.entity_id = org.id and email.rn = 1
left outer join phone on phone.entity_id = org.id and phone.rn = 1
left outer join fias_addresses fa on org.id_fias_address = fa.id
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
