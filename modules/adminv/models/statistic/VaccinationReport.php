<?php

namespace app\modules\adminv\models\statistic;

use app\models\db\Diseases;
use app\models\db\Pets;
use app\models\db\Species;
use app\models\db\ViolationType;
use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;

class VaccinationReport extends Model
{
    const
        VACCINE_RABBICAN_NAME = 'Рабикан Вакцина антирабическая инактивированная сухая культуральная из штамма «Щелково-51»';

    public $from;
    public $to;
    public $organizations;
    public $areas;

    public function rules()
    {
        return [
            ['from', 'date', 'format' => 'php:Y-m-d'],
            ['to', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @return array
     */
    public function search()
    {
        $rabiesId = Diseases::find()
            ->select('id')
            ->where(['name' => 'Бешенство (Rabies)'])
            ->scalar();

        // сказано не учитывать valid_until, учитывать дату вакцинации и конец диапазона
        $expireCompare = date_create_from_format('Y-m-d', $this->to)->modify('-12 months')->format('Y-m-d');

        $query = (new Query())
            ->from(Pets::tableName())
            ->select([
                'areas.id as id_area',
                'areas.name',
                'organizations.id',
                'organizations.short_name',
                //животных на учете
                new Expression('sum(case when species.tech_name = :cat then 1 else 0 end) as total_cats',
                    [':cat' => Species::TECH_NAME_CAT]),
                new Expression('sum(case when species.tech_name = :dog then 1 else 0 end) as total_dogs',
                    [':dog' => Species::TECH_NAME_DOG]),
                new Expression('sum(case when (species.tech_name not in (:cat, :dog) 
                                                or species.tech_name is null) then 1 else 0 end) as total_other',
                    [':cat' => Species::TECH_NAME_CAT, ':dog' => Species::TECH_NAME_DOG]),
                //привиты рабиканом
                new Expression('sum(case when species.tech_name = :cat and result.drug_name = :rabican then 1 else 0 end) as total_rabies_cats',
                    [':cat' => Species::TECH_NAME_CAT, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                new Expression('sum(case when species.tech_name = :dog and result.drug_name = :rabican then 1 else 0 end) as total_rabies_dogs',
                    [':dog' => Species::TECH_NAME_DOG, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                new Expression('sum(case when (species.tech_name not in (:cat, :dog) 
                                                or species.tech_name is null) and result.drug_name = :rabican then 1 else 0 end) as total_rabies_other',
                    [':cat' => Species::TECH_NAME_CAT, ':dog' => Species::TECH_NAME_DOG, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                //привиты комплексной вакциной
                new Expression('sum(case when species.tech_name = :cat and result.drug_name != :rabican then 1 else 0 end) as total_complex_cats',
                    [':cat' => Species::TECH_NAME_CAT, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                new Expression('sum(case when species.tech_name = :dog and result.drug_name != :rabican then 1 else 0 end) as total_complex_dogs',
                    [':dog' => Species::TECH_NAME_DOG, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                new Expression('sum(case when (species.tech_name not in (:cat, :dog) 
                                                or species.tech_name is null) and result.drug_name != :rabican then 1 else 0 end) as total_complex_other',
                    [':cat' => Species::TECH_NAME_CAT, ':dog' => Species::TECH_NAME_DOG, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                //отказы
                new Expression('sum(case when species.tech_name = :cat
                                                and rejection.rejected = 1 then 1 else 0 end) as total_reject_cats',
                    [':cat' => Species::TECH_NAME_CAT, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                new Expression('sum(case when species.tech_name = :dog
                                                and rejection.rejected = 1 then 1 else 0 end) as total_reject_dogs',
                    [':dog' => Species::TECH_NAME_DOG, ':rabican' => self::VACCINE_RABBICAN_NAME]),
                new Expression('sum(case when (species.tech_name not in (:cat, :dog)
                                                or species.tech_name is null)
                                                and rejection.rejected = 1 then 1 else 0 end) as total_reject_other',
                    [':cat' => Species::TECH_NAME_CAT, ':dog' => Species::TECH_NAME_DOG, ':rabican' => self::VACCINE_RABBICAN_NAME]),
            ])
            ->leftJoin('species', 'species.id = pets.id_species')
            ->leftJoin(
                ['result' => new Expression('(SELECT DISTINCT ON (id_pet) pet_vaccination.id_pet, pet_vaccination.drug_name, max(pet_vaccination.valid_until) as validity
                    FROM (
                             select pv.id_pet, pv.valid_until, pv.drug_name
                             from pet_rabies_vaccination pv
                                      inner join tmc.tmc_to_diseases td on pv.id_vaccine = td.id_tmc and td.id_disease = ' . $rabiesId . '
                             and "date" between \'' . $this->from . '\' and \'' . $this->to . '\'
                             and "date" > \''. $expireCompare . '\'
                             union all
                             select pv.id_pet, pv.valid_until, pv.drug_name
                             from pet_other_vaccinations pv
                                      inner join tmc.tmc_to_diseases td on pv.id_vaccine = td.id_tmc and td.id_disease = ' . $rabiesId . '
                             and "date" between \'' . $this->from . '\' and \'' . $this->to . '\'
                             and "date" > \''. $expireCompare . '\'
                         ) pet_vaccination
                    group by id_pet, drug_name)')],
                'result.id_pet = pets.id'
            )
            ->leftJoin('organizations', 'organizations.id = pets.id_reg_organization')
            ->leftJoin('pets_to_owner pown', 'pown.id_pet = pets.id and pown.id_owner_type = 1')
            ->leftJoin('pet_owners own', 'own.id = pown.id_owner and own.is_deleted = false')
            ->leftJoin('fias_addresses fias', 'coalesce(own.id_fact_fias_address, own.id_fias_address) = fias.id')
            ->leftJoin('areas', 'areas.id = fias.id_area')
            ->leftJoin(
                ['rejection' => new Expression('(SELECT DISTINCT id_pet, 1 as rejected
                    FROM "violation"
                             LEFT JOIN "violation_type" ON violation_type.id_type = violation.id_type
                    WHERE violation_type.tech_name = \'V04_VACCINATION_REJECTION\'
                      AND violation.date_violation
                        between \'' . $this->from . ' 00:00:00' . '\' and \'' . $this->to . ' 23:59:59' . '\')'),
                ],
                'rejection.id_pet = pets.id'
            )
            ->where([
                'and',
                ['<=', 'pets.reg_date', $this->to],
                ['pets.reg_expire_date' => null],
                ['not', ['pets.id_reg_organization' => null]],
            ])
            ->groupBy([
                'organizations.id',
                'organizations.short_name',
                'areas.name',
                'areas.id'
            ])
            ->orderBy([
                'areas.name' => SORT_ASC,
                'organizations.short_name' => SORT_ASC
            ]);

        if (!empty($this->areas)) {
            $query->andWhere(['fias.id_area' => $this->areas]);
        }

        if (!empty($this->organizations)) {
            $query->andWhere(['pets.id_reg_organization' => $this->organizations]);
        }

        return [
            'from' => $this->from,
            'to' => $this->to,
            'data' => $query->all()
        ];
    }
}
