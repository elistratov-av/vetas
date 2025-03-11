<?php

use app\commands\migrate\Migration;

/**
 * Class m190702_085120_animalid_remove_owner_from_pets_view
 */
class m190702_085120_animalid_remove_owner_from_pets_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('drop view IF EXISTS animalid.view_pets');

        $sql = <<<SQL
create or replace view animalid.view_pets as
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
  pt.id_reg_organization as companyId

from public.pets pt, public.pet_identification pi,
  animalid.view_companys co, breeds bd, species sp
where pi.id_pet = pt.id
      and pi.main_flag = true
      and pi.id_ident_type = 1
      and pt.sex is not null
      and pt.birthday is not null
      and pt.id_reg_organization is not null
      and pt.id_reg_organization = co.id
      and pt.id_breed = bd.id
      and pt.id_species = sp.id;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190702_085120_animalid_remove_owner_from_pets_view cannot be reverted.\n";

        return false;
    }
}
