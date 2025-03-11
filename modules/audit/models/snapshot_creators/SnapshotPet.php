<?php


namespace app\modules\audit\models\snapshot_creators;

use app\models\db\Pets;
use yii\db\ActiveQuery;

class SnapshotPet extends GenericSnapshot
{
    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        return 3;
    }

    /**
     * Возвращает слепок из бд
     *
     * @param integer $parent_entity_id
     * @return array|null
     */
    protected function queryFromBD($parent_entity_id)
    {
        return Pets::find()
            ->select([
                'pets.id',
                'pets.reg_expire_date',
                'pets.birthday',
                'pets.name',
                'pets.sex',
                'pets.id_reg_organization',
                'pets.id_reg_expire_reason',
                'pets.reg_date',
                'pets.guide_dog',
                'pets.castrated',
                'pets.date_plan_rabies_vaccination',
                'pets.date_plan_identification',
                'pets.date_plan_lept_vaccination',
                'pets.color',
                'pets.characteristics',
                'pets.id_created_organization',
                'pets.is_main',
                'pets.id_main_pet',
                'pets.duble_validation',
                'pets.id_relocate',
                'pets.id_fias_address',

                'species.name AS species_name',
                'breeds.name AS breeds_name',

                'reg_expire_reasons.name AS reg_expire_reason',
            ])
            ->leftJoin('species', 'species.id = pets.id_species')
            ->leftJoin('breeds', 'breeds.id = pets.id_breed')
            ->leftJoin('reg_expire_reasons', 'reg_expire_reasons.id = pets.id_reg_expire_reason')
            ->with(['pet_rabies_vaccinations' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select([
                    'pet_rabies_vaccination.id',
                    'pet_rabies_vaccination.id_pet',
                    'pet_rabies_vaccination.id_vaccine',
                    'pet_rabies_vaccination.drug_name',
                    'pet_rabies_vaccination.producer_name',
                    'pet_rabies_vaccination.batch',
                    'pet_rabies_vaccination.production_date',
                    'pet_rabies_vaccination.expiry_date',
                    'pet_rabies_vaccination.date',
                    'pet_rabies_vaccination.valid_until',
                    'pet_rabies_vaccination.id_organization',
                    'pet_rabies_vaccination.id_specialist',
                ]);
            }])
            ->with(['pet_dehelmintizations' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select([
                    'pet_dehelmintization.id',
                    'pet_dehelmintization.id_pet',
                    'pet_dehelmintization.id_drug',
                    'pet_dehelmintization.drug_name',
                    'pet_dehelmintization.producer_name',
                    'pet_dehelmintization.date',
                    'pet_dehelmintization.id_organization',
                    'pet_dehelmintization.id_specialist',
                ]);
            }])
            ->with(['pet_ectoparasites' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select([
                    'pet_ectoparasites.id',
                    'pet_ectoparasites.id_pet',
                    'pet_ectoparasites.id_drug',
                    'pet_ectoparasites.drug_name',
                    'pet_ectoparasites.producer_name',
                    'pet_ectoparasites.date',
                    'pet_ectoparasites.id_organization',
                    'pet_ectoparasites.id_specialist',
                ]);
            }])
            ->with(['pet_other_vaccinations' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select([
                    'pet_other_vaccinations.id',
                    'pet_other_vaccinations.id_pet',
                    'pet_other_vaccinations.id_vaccine',
                    'pet_other_vaccinations.drug_name',
                    'pet_other_vaccinations.producer_name',
                    'pet_other_vaccinations.batch',
                    'pet_other_vaccinations.production_date',
                    'pet_other_vaccinations.expiry_date',
                    'pet_other_vaccinations.date',
                    'pet_other_vaccinations.valid_until',
                    'pet_other_vaccinations.id_organization',
                    'pet_other_vaccinations.id_specialist',
                ]);
            }])
            ->with(['pet_identification' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select([
                    'pet_identification.id',
                    'pet_identification.id_pet',
                    'pet_identification.identification_code',
                    'pet_identification.main_flag',
                    'identification_types.name AS type',
                ])->leftJoin(
                    'identification_types',
                    'pet_identification.id_ident_type = identification_types.id'
                );
            }])
            ->with(['fias_address' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select('fias_addresses.region');
                $query->select('fias_addresses.city');
                $query->select('fias_addresses.street');
                $query->select('fias_addresses.house');
                $query->select('fias_addresses.full_address');
            }])
            ->with(['pets_to_owner' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select([
                    'pets_to_owner.id_pet',
                    'pet_owners.id',
                    'pet_owners.f_fio',
                    'pet_owners.i_fio',
                    'pet_owners.o_fio',
                    'pet_owners.jur_name',
                    'pet_owners.inn',
                    'pet_owners.ogrn',
                    'pet_owners.birthday',
                    'pet_owners.snils',
                    'pet_owners.is_legal',
                    'pet_owners.id_area',
                    'pet_owners.id_district',
                    'pet_owners.is_deleted',
                    'pet_owners.entrepreneur',
                    'pet_owners.fullname',

                    'fias_addresses.full_address AS fias_addresses',
                    'fact_fias_addresses.full_address AS fact_fias_addresses',

                    'pet_owner_type.name AS pet_owner_type',

                ])
                    ->leftJoin('pet_owners', 'pets_to_owner.id_owner = pet_owners.id')
                    ->leftJoin(
                        'fias_addresses',
                        'pet_owners.id_fias_address = fias_addresses.id'
                    )->leftJoin(
                        'fias_addresses AS fact_fias_addresses',
                        'pet_owners.id_fact_fias_address = fact_fias_addresses.id'
                    )
                    ->leftJoin('pet_owner_type', 'pet_owner_type.id = pets_to_owner.id_owner_type');
            }])
            ->where(['pets.id' => $parent_entity_id])
            ->asArray()
            ->one();
    }
}
