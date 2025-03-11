<?php

use app\commands\migrate\Migration;

/**
 * Class m191031_114630_edit_animalid_companies_view
 */
class m191031_114630_edit_animalid_companies_pets_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
//        $this->execute('drop view animalid.view_pets');
//        $this->execute('drop view animalid.view_companys');

        $sql = <<<SQL
create or replace view animalid.view_companies as
  WITH phones AS (
      SELECT
        contacts.id,
        contacts.name,
        contacts.entity_id AS org
      FROM (contacts
        LEFT JOIN contact_types c2 ON ((contacts.id_contact_type = c2.id)))
      WHERE (((contacts.entity_type) :: text = 'organization' :: text) AND ((c2.type) :: text = 'phone' :: text))
  ), emails AS (
      SELECT
        contacts.id,
        contacts.name,
        contacts.entity_id AS org
      FROM (contacts
        LEFT JOIN contact_types c2 ON ((contacts.id_contact_type = c2.id)))
      WHERE (((contacts.entity_type) :: text = 'organization' :: text) AND ((c2.type) :: text = 'email' :: text))
  )
  SELECT
    orgs.id,
    orgs.short_name                                                                          AS org_name,
    phones.name                                                                              AS phone,
    COALESCE(emails.name, (concat((orgs.id) :: text, '@vetas.mos.ru')) :: text)              AS email,
    fa.full_address                                                                          AS address,
    orgs.created_at                                                                          AS createdate,
    orgs.updated_at                                                                          AS upddate
  FROM (((organizations orgs
    LEFT JOIN phones ON ((phones.org = orgs.id)))
    LEFT JOIN emails ON ((emails.org = orgs.id)))
    LEFT JOIN fias_addresses fa ON ((orgs.id_fias_address = fa.id)))
UNION ALL
  SELECT
    companies.id * (-1)                                                                      AS id,
    companies.fullname                                                                       AS org_name,
    companies.phone                                                                          AS phone,
    companies.email                                                                          AS email,
    companies.address                                                                        AS address,
    companies.created_at                                                                     AS createdate,
    companies.updated_at                                                                     AS upddate
  FROM companies;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
create or replace view animalid.view_pets as
  SELECT
    pt.id,
    pi.identification_code                              AS chip,
    pi.created_at                                       AS chipdate,
    pt.id_species                                       AS kindid,
    pt.id_breed                                         AS breedid,
    sp.name                                             AS kind,
    bd.name                                             AS breed,
    CASE
    WHEN ((pt.sex) :: text = 'm' :: text)
      THEN 'самец' :: text
    WHEN ((pt.sex) :: text = 'f' :: text)
      THEN 'самка' :: text
    ELSE NULL :: text
    END                                                 AS sex,
    (pt.birthday) :: timestamp without time zone        AS birthday,
    pt.created_at                                       AS createdate,
    pt.updated_at                                       AS upddate,
    coalesce(pi.identif_org, pi.identif_comp * (-1))    AS companyid
  FROM pets pt,
    pet_identification pi,
    animalid.view_companies co,
    breeds bd,
    species sp
  WHERE ((pi.id_pet = pt.id) AND (pi.main_flag = true) AND (pi.id_ident_type = 1) AND (pt.sex IS NOT NULL) AND
         (pt.birthday IS NOT NULL) AND (pt.id_reg_organization IS NOT NULL) AND (pt.id_reg_organization = co.id) AND
         (pt.id_breed = bd.id) AND (pt.id_species = sp.id))
  GROUP BY pt.id, pi.identification_code, pi.created_at, pt.id_species, pt.id_breed, sp.name, bd.name, pi.identif_org, pi.identif_comp
  ORDER BY pt.id;

SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m191031_114630_edit_animalid_companies_view cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191031_114630_edit_animalid_companies_view cannot be reverted.\n";

        return false;
    }
    */
}
