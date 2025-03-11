<?php


namespace app\modules\audit\models\snapshot_creators;

use app\models\db\PetOwners;
use yii\db\ActiveQuery;

class SnapshotPetOwner extends GenericSnapshot
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
        return PetOwners::find()
            ->select([
                'pet_owners.id',
                'pet_owners.f_fio',
                'pet_owners.i_fio',
                'pet_owners.o_fio',
                'pet_owners.jur_name',
                'pet_owners.inn',
                'pet_owners.ogrn',
                'pet_owners.birthday',
                'pet_owners.snils',
                'pet_owners.id_fact_address',
                'pet_owners.is_legal',
                'pet_owners.fullname',
                'pet_owners.id_area',
                'pet_owners.id_district',
                'pet_owners.id_fias_address',
                'pet_owners.id_fact_fias_address',
                'pet_owners.is_deleted',
                'pet_owners.entrepreneur',
                'pet_owners.sso_id',
                'pet_owners.is_main',
                'pet_owners.id_main_owner',
                'pet_owners.duble_validation',

                'fias_addresses.full_address AS fias_addresses',
                'fact_fias_addresses.full_address AS fact_fias_addresses',
            ])
            ->with(['contacts' => function ($query) {
                /** @var $query ActiveQuery * */
                $query
                    ->select([
                        'contacts.id',
                        'contacts.entity_id',
                        'contacts.name AS name',
                        'contacts.confirmed',
                        'contacts.main_flag',
                        'contact_types.name AS type',
                    ])
                    ->leftJoin(
                        'contact_types',
                        'contacts.id_contact_type = contact_types.id'
                    );
            }])
            ->leftJoin(
                'fias_addresses',
                'pet_owners.id_fias_address = fias_addresses.id'
            )
            ->leftJoin(
                'fias_addresses AS fact_fias_addresses',
                'pet_owners.id_fact_fias_address = fact_fias_addresses.id'
            )
            ->with(['pets' => function ($query) {
                /** @var $query ActiveQuery * */
                $query->select([
                    'pets.id',
                    'pets.reg_expire_date',
                    'pets.birthday',
                    'pets.name',
                    'pets.sex',

                    'reg_expire_reasons.name AS reg_expire_reason',

                    'species.name AS species_name',
                    'breeds.name AS breeds_name',

                    'pet_owner_type.name AS pet_owner_type',
                ])
                    ->leftJoin('species', 'species.id = pets.id_species')
                    ->leftJoin('breeds', 'breeds.id = pets.id_breed')
                    ->leftJoin('reg_expire_reasons', 'reg_expire_reasons.id = pets.id_reg_expire_reason')
                    ->leftJoin('pets_to_owner', 'pets_to_owner.id_pet = pets.id')
                    ->leftJoin('pet_owner_type', 'pet_owner_type.id = pets_to_owner.id_owner_type');
            }])
            ->where(['pet_owners.id' => $parent_entity_id])
            ->asArray()
            ->one();
    }
}
