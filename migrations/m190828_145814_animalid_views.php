<?php

use app\commands\migrate\Migration;

/**
 * Class m190828_135814_rebuild_animalid_companys_view
 */
class m190828_145814_animalid_views extends Migration
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
    orgs.short_name                                                                          AS name,
    phones.name                                                                              AS phone,
    COALESCE(emails.name, (concat((orgs.id) :: text, '@vetas.mos.ru')) :: text) AS email,
    fa.full_address                                                                          AS address,
    orgs.created_at                                                                          AS createdate,
    orgs.updated_at                                                                          AS upddate
  FROM (((organizations orgs
    LEFT JOIN phones ON ((phones.org = orgs.id)))
    LEFT JOIN emails ON ((emails.org = orgs.id)))
    LEFT JOIN fias_addresses fa ON ((orgs.id_fias_address = fa.id)))
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
        echo "m190828_135814_rebuild_animalid_companys_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190828_135814_rebuild_animalid_companys_view cannot be reverted.\n";

        return false;
    }
    */
}
