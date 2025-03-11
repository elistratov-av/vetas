<?php

use app\commands\migrate\Migration;

/**
 * Class m190124_091123_clean_pet_and_owners
 */
class m190126_091123_clean_pet_and_owners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE public.visits_gov_services DROP CONSTRAINT "fk-visits_gov_services-id_visit"');
        $this->execute('ALTER TABLE public.visits_gov_services ADD CONSTRAINT "fk-visits_gov_services-id_visit"
FOREIGN KEY (id_visit) REFERENCES public.visits (id) ON DELETE CASCADE');

        $this->execute('ALTER TABLE public.visits_specialists DROP CONSTRAINT "fk-visits_specialists-id_visit"');
        $this->execute('ALTER TABLE public.visits_specialists ADD CONSTRAINT "fk-visits_specialists-id_visit"
FOREIGN KEY (id_visit) REFERENCES public.visits (id) ON DELETE CASCADE;');

        $this->execute('ALTER TABLE public.pets_to_owner DROP CONSTRAINT "fk-pets_to_owner-id_pet"');
        $this->execute('ALTER TABLE public.pets_to_owner ADD CONSTRAINT "fk-pets_to_owner-id_pet"
FOREIGN KEY (id_pet) REFERENCES public.pets (id) ON DELETE CASCADE');

        $this->execute('ALTER TABLE public.reg_certificates DROP CONSTRAINT "fk-reg_certificates-pet"');
        $this->execute('ALTER TABLE public.reg_certificates ADD CONSTRAINT "fk-reg_certificates-pet"
FOREIGN KEY (id_pet) REFERENCES public.pets (id) ON DELETE CASCADE');

        $this->execute('ALTER TABLE public.reg_certificates DROP CONSTRAINT "fk-reg_certificates-owner"');
        $this->execute('ALTER TABLE public.reg_certificates ADD CONSTRAINT "fk-reg_certificates-owner"
FOREIGN KEY (id_owner) REFERENCES public.pet_owners (id) ON DELETE CASCADE');

        $this->execute('ALTER TABLE public.pet_owners_history DROP CONSTRAINT "fk-pet_owners_history-id_pet"');
        $this->execute('ALTER TABLE public.pet_owners_history ADD CONSTRAINT "fk-pet_owners_history-id_pet"
FOREIGN KEY (id_pet) REFERENCES public.pets (id) ON DELETE CASCADE');

        $this->execute('delete from pets
where pets.id_reg_expire_reason = (select id from reg_expire_reasons where name = \'по инициативе владельца\')');


        $this->execute('delete from pet_owners
where pet_owners.id not in (select pets_to_owner.id_owner from pets_to_owner)');

        $sql = <<<SQL
select identification_code,
  count(id),
  max(id_pet) filter (where id_ident_type is not null) as origin,
  array_agg(id_pet) filter (where id_ident_type is null) as shadows
from temp.pets_old_identification
where identification_code is not null
group by identification_code
having count(id) > 1 and count(id_ident_type) = 1
SQL;


        $res = $this->db->createCommand($sql)->queryAll();

        if(empty($res))  false;

        foreach ($res as $item){
            $shadows = explode(',', trim($item['shadows'], '{}'));
            $origin = $item['origin'];
            foreach ($shadows as $shadow){
                $this->update('visits', ['id_pet' => $origin], ['id_pet' => $shadow]);

                $this->delete('pets', ['id' => $shadow]);
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190124_091123_clean_pet_and_owners cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190124_091123_clean_pet_and_owners cannot be reverted.\n";

        return false;
    }
    */
}
