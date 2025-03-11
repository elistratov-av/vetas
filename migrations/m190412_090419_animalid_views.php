<?php

use app\commands\migrate\Migration;

/**
 * Class m190321_090419_animalid_views
 */
class m190412_090419_animalid_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
create or replace view animalid.view_owners as      
with co as
(
    select co.*, ct.type
    from contacts co , contact_types ct
    where  co.id_contact_type = ct.id
            and (ct.type = 'phone' or ct.type = 'email')
            and ct.entity_type = 'pet_owner'
            and co.entity_type = 'pet_owner'
            and co.main_flag = true
)
select po.id, po.fullname,
  co.name as email,
  com.name as phone,
  fa.full_address as address
from pet_owners po
  join co on po.id = co.entity_id and co.type = 'email'
  left outer join co as com on po.id = com.entity_id and com.type = 'phone'
  left outer join fias_addresses fa on po.id_fact_fias_address = fa.id
where po.fullname is not null 
SQL;
        $this->execute($sql);

        $sql = <<<SQL
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
  fa.full_address as address
from organizations po
  join co on po.id = co.entity_id and co.type = 'email'
  left outer join co as com on po.id = com.entity_id and com.type = 'phone'
  left outer join fias_addresses fa on po.id_fias_address = fa.id
SQL;
        $this->execute($sql);

        $sql = <<<SQL
create or replace view animalid.view_kinds as
select id, name from species
SQL;

        $this->execute($sql);

        $sql = <<<SQL
create or replace view animalid.view_breeds as
select id, name, species_id as kind  from breeds
SQL;

        $this->execute($sql);

        $sql = <<<SQL
create view animalid.view_pets as         
    select pt.id,
      pi.identification_code as chip,
      pi.created_at::timestamp as chipdate,
      pt.id_species as kindId,
      pt.id_breed as breedId,
      sp.name as kind,
      bd.name as breed,
      case  when pt.sex = 'm' then 'самец'
      when pt.sex = 'f' then 'самка'
      end as sex,
      pt.birthday::timestamp,
      pt.created_at::timestamp as createdate,
      pt.updated_at::timestamp as upddate,
      pt.id_reg_organization as companyId,
      po.id_owner as ownerId
    
    from public.pets pt, public.pet_identification pi, public.pets_to_owner po,
      animalid.view_companys co, animalid.view_owners ow, breeds bd, species sp
    where pi.id_pet = pt.id
          and pi.main_flag = true
          and pi.id_ident_type = 1
          and po.id_pet = pt.id
          and po.id_owner_type = 1
          and pt.sex is not null
          and pt.birthday is not null
          and pt.id_reg_organization is not null
          and po.id_owner = ow.id
          and pt.id_reg_organization = co.id
          and pt.id_breed = bd.id
          and pt.id_species = sp.id
SQL;

        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190321_090419_animalid_views cannot be reverted.\n";

        return false;
    }
    */
}
