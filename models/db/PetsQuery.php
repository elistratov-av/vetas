<?php

namespace app\models\db;

use app\modules\v2\modules\gosvetnadzor\cron\ViolationCheckVaccination;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class PetsQuery
 * @package app\models\db
 * @see Pets
 */
class PetsQuery extends ActiveQuery
{
    /**
     * @param int|array|null $id_species
     * @return $this
     */
    public function forInformation($id_species = null)
    {
        $contactsSql = <<<SQL
select 1 from contacts
inner join pet_owners on pet_owners.id = contacts.entity_id and contacts.entity_type = 'pet_owner'
inner join pets_to_owner on pets_to_owner.id_owner = pet_owners.id
where 
  pets_to_owner.id_pet = pets.id 
  and pets_to_owner.id_owner_type = 1
  and (pet_owners.is_main = true or pet_owners.is_main isnull)
limit 1  
SQL;

        if ($id_species === null) {
            $id_species = [9, 25];
        }

        return $this->andWhere(['id_species' => $id_species])
            ->andWhere(new Expression("(pets.is_main = true or pets.is_main isnull)"))
            ->andWhere(new Expression("reg_expire_date is null"))
            ->andWhere(new Expression("exists ($contactsSql)"));
    }

    /**
     * @return $this
     */
    public function withoutIdentification()
    {
        return $this->andWhere(new Expression("not exists (
            select 1 from pet_identification 
            where pet_identification.id_pet = pets.id and pet_identification.id_ident_type = 1
            limit 1
        )"));
    }

    /**
     * @return PetsQuery
     * @throws \Exception
     */
    public function withRabiesVaccination()
    {
        $vaccinationSql = <<<SQL
select t1.*, (date + make_interval(days => 337))::date as inform_date from (
    select 
        id_pet, max(date) as date
    from pet_rabies_vaccination
    group by pet_rabies_vaccination.id_pet
) as t1
where
    :now::date = (t1.date + make_interval(days => 337))::date
SQL;

        return $this->innerJoin(
                "($vaccinationSql) as rabies_vaccination",
                'rabies_vaccination.id_pet = pets.id',
                [
                    ':now' => (new \DateTime())->format('Y-m-d'),
                ]
            );
    }

    /**
     * @return PetsQuery
     * @throws \Exception
     */
    public function withLeptospirosisVaccination()
    {
        $vaccinationSql = <<<SQL
select t1.*, (date + make_interval(days => 337))::date as inform_date from (
    select 
        id_pet, max(date) as date
    from pet_other_vaccinations
    where id_vaccine in (
        select id from vaccines 
        where id in (
            select id_vaccine 
            from vaccines_to_diseases 
            join diseases on diseases.id = vaccines_to_diseases.id_disease where diseases.name = :disease_name
        )
    )
    group by pet_other_vaccinations.id_pet
) as t1
where
    :now::date = (t1.date + make_interval(days => 337))::date
SQL;

        return $this->innerJoin(
            "($vaccinationSql) as leptospirosis_vaccination",
            'leptospirosis_vaccination.id_pet = pets.id',
            [
                ':now' => (new \DateTime())->format('Y-m-d'),
                ':disease_name' => ViolationCheckVaccination::QUERY_CONSTANTS_DISEASES_NAME_LEPTOSPIROSIS
            ]
        );
    }

    /**
     * @param string $type
     * @return $this
     */
    public function withInformationLog(string $type)
    {
        return $this->leftJoin(
        "(
                select pet_id, owner_id, max(date::date) as date 
                from subscription.information 
                where type = '{$type}' 
                group by pet_id, owner_id, type
            ) as information_log",
        'information_log.pet_id = pets.id'
        );
    }
}
