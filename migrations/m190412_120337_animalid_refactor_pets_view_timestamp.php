<?php

use app\commands\migrate\Migration;

/**
 * Class m190412_120337_aniamid_refacor_pets_view_timestamp
 */
class m190412_120337_animalid_refactor_pets_view_timestamp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('drop view if exists animalid.view_pets');

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
      pt.birthday::timestamp(0),
      pt.created_at::timestamp(0) as createdate,
      pt.updated_at::timestamp(0) as upddate,
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
        echo "m190412_120337_aniamid_refacor_pets_view_timestamp cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190412_120337_aniamid_refacor_pets_view_timestamp cannot be reverted.\n";

        return false;
    }
    */
}
