<?php

namespace app\modules\v2\modules\vaccinationJournal\models;

use app\common\efsp\EfspWrapper;
use app\common\validators\FullTrimValidator;
use app\models\db\Contacts;
use app\models\db\Diseases;
use app\models\db\FiasAddresses;
use app\models\db\OutsideOrg;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetOwners;
use app\models\db\PetOwnerType;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\Quarantine;
use app\models\db\RegExpireReasons;
use app\models\db\Species;
use app\models\db\tmc\Balance;
use app\models\db\tmc\Dosages;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcVaccine;
use app\models\db\Violation;
use app\models\db\ViolationType;
use app\models\db\Visits;
use app\modules\admin\models\PetsToOwner;
use app\modules\v2\common\skeletons\CommonList;
use app\modules\v2\modules\petOwners\models\ContactsModel;
use app\modules\v2\modules\pets\models\IdentModel;
use app\modules\v2\modules\pets\models\PetToOwnerModel;
use app\modules\v2\modules\pets\models\VaccinationModel;
use app\modules\v2\modules\quarantine\models\QuarantineModel;
use app\modules\v2\modules\visit\models\ServiceTmcsModelSave;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use app\models\db\QuarantineDetourNonVisit;
use yii\web\NotFoundHttpException;

/**
 * Class FlatModel
 *
 * @package app\modules\v2\modules\vaccinationJournal\models
 */
class FlatModel
{
    const NONE = 0;
    const PET_OUT = 10;
    const PET_IN_HOSPITAL = 20;
    const PET_IS_DEATH = 30; // Падеж, снять с учёта
    const LIVING_SPACE_HAS_NO_PET = 40; // Животное без квартиры, пишем в QuarantineDetourNonVisit для статистики
    const OWNER_REFUSED = 50;
    const PET_HAS_OTHER_OWNER = 60; // Текущий вледелец не является основным
    const PET_NOT_EXISTS = 70; // Животное не существует (заведено по "приколу" через Мос.ру, тестовое и т.п.), снять с учёта
    const PET_HAS_OUTSIDE_ORG_VACCINE = 80; // Отметка о том, что по животному занесены данные о вакцине из сторонней организации при обходе для статистики

    /**
     * @return array
     */
    private static function getIncludes($filter = []): array
    {
        $includes = [
            'pet_identification',
            'anamnesis',
            'health',
            'breeds',
            'species',
            'violations'  => null,
            'pet_ectoparasites',
            'pet_other_vaccinations',
            'pet_rabies_vaccinations',
            'visitDetour' => null,
            'quarantineFocus',
            'quarantineFocus.quarantine',
            'visitDetour.services',
            'visitDetour.specialists',
            'visitDetour.author_ref',
            'visitDetour.author_ref.organization',
            'visitDetour.author_ref.user',
            'visitDetour.visitServiceTmcs',
            'visitDetour.visitServiceTmcs.balance',
            'visitDetour.visitServiceTmcs.balance.organization',
            'visitDetour.visitServiceTmcs.balance.production_form',
            'visitDetour.visitServiceTmcs.balance.tmc',
            'visitDetour.visitServiceTmcs.balance.tmc.measure',
            'visitDetour.visitServiceTmcs.tmc',
            'visitDetour.visitServiceTmcs.dosage',
            'visitDetour.visitServiceTmcs.dosage.measure',
            'visitDetour.visitServiceTmcPet',
            'visitDetour.pets',
            'owner',
            'owner.fact_fias_addresses',
            'owner.fias_addresses',
            'owner.contacts',
            'owner.phoneContacts',
            'owner.emailContacts',
            'quarantine_detour_non_visit',
        ];

        $includes['violations'] = function (ActiveQuery $query) use ($filter) {
            if (!empty($filter['detour_from'])) {
                $query->andWhere(['>=', 'date_violation', $filter['detour_from']]);
            }
            if (!empty($filter['detour_till'])) {
                $query->andWhere(['<=', 'date_violation', $filter['detour_till']]);
            }

            $query
                ->andWhere([
                    'state' => Violation::ACTIVE_STATES,
                ])
                ->orderBy('date_violation desc');
        };

        if (!empty($filter['quarantine_zone_id'])) {
            $includes['visitDetour'] = function (ActiveQuery $query) use ($filter) {
                if (!empty($filter['detour_from'])) {
                    $query->andWhere(['>=', 'visits.fact_start_dttm', $filter['detour_from']]);
                }
                if (!empty($filter['detour_till'])) {
                    $query->andWhere(['<=', 'visits.fact_start_dttm', $filter['detour_till'] . ' 23:59:59']);
                }

                $query
                    ->andWhere([
                        'id_quarantine' => $filter['quarantine_zone_id'],
                    ])
                    ->orderBy('fact_start_dttm desc');
            };
        }

        return $includes;
    }

    public static function get(int $id): ?array
    {
        $pet = Pets::find()
            ->with(self::getIncludes())
            ->where(['pets.id' => $id])
            ->asArray()
            ->one();

        return self::setFullNameToSpecialist($pet);
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @param bool $isStatistics
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public static function getAll(
        int $page = 1,
        int $limit = 10,
        array $filter = [],
        bool $isStatistics = false
    ): CommonList {
        $filter = self::validateFilter($filter);

        $query = Pets::find()
            ->select([
                'pets.id id',
                'id_breed',
                'id_species',
                'pets.birthday birthday',
                'pets.name name',
                'sex',
                'size_id',
                'pets.description description',
            ])
            ->where(['id_pet_tmp' => null])
        ;

        if (!empty($filter)) {
            $query = self::applyFilter($query, $filter, $isStatistics);
        }

        $pets_count = clone($query);
        $pets = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->asArray()
            ->all();

        return new CommonList(
            'pets',
            self::preparePetsData($pets),
            $pets_count->count(),
            $page,
            $limit
        );
    }

    /**
     * Обрабатываем данные о визите.
     *
     * @param $pets
     */
    private static function preparePetsData($pets)
    {
        if (!$pets) {
            return [];
        }

        foreach ($pets as &$pet) {
            if (isset($pet['visitDetour'])){
                if ( count($pet['visitDetour']) === 0) {
                    $pet['violations'] = [];
                } else {
                    $visitId = $pet['visitDetour'][0]['id'];
                    $pet['violations'] = array_filter($pet['violations'], function ($violation) use ($visitId) {
                        return $violation['id_visit'] == $visitId;
                    });

                    $pet = self::setFullNameToSpecialist($pet);
                }
            }
        }

        return $pets;
    }

    /**
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    private static function validateFilter(array $filter): array
    {
        $emptyFilter = [
            'quarantine_zone_id'     => null,
            'area'                   => null,
            'district'               => null,
            'address'                => null,
            'diseases'               => [],
            'quarantine_from'        => null,
            'quarantine_till'        => null,
            'detour_from'            => null,
            'detour_till'            => null,
            'address_without_detour' => null,
            'pet_owner'              => null,
            'pet'                    => null,
            'why_not_available'      => null,
            'is_not_available'       => null,
            'species'                => null,
            'is_vaccinated'          => null,
            'is_refused'             => null,
            'specialist_id'          => null,
            'vaccine_till'           => null,
        ];
        $filter = array_merge($emptyFilter, $filter);

        $rules = [
            [['quarantine_zone_id'], 'required'],
            [['quarantine_zone_id', 'specialist_id'], 'integer'],
            [['area', 'district', 'address', 'pet_owner', 'pet'], 'string'],
            [['area', 'district', 'address', 'pet_owner', 'pet'], FullTrimValidator::class],
            [['address_without_detour', 'is_not_available', 'is_vaccinated', 'is_refused'], 'boolean'],
            ['diseases', 'each', 'rule' => ['integer']],
            ['species', 'each', 'rule' => ['integer']],
            [['quarantine_from', 'quarantine_till', 'detour_from', 'detour_till', 'vaccine_till'], 'date', 'format' => 'php:Y-m-d'],
            ['why_not_available', 'each', 'rule' => ['in', 'range' => QuarantineDetourNonVisit::absenceReasons()]],
        ];

        $model = DynamicModel::validateData($filter, $rules);
        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);

            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors))
            );
        }

        return $model->attributes;
    }

    /**
     * @param ActiveQuery $query
     * @param array $filter
     * @param bool $isStatistics
     * @param bool $isActList
     * @return ActiveQuery
     */
    private static function applyFilter(ActiveQuery $query, array $filter, bool $isStatistics = false, bool $isActList = false): ActiveQuery
    {
        $model = new QuarantineModel();
        $quarantineId = $filter['quarantine_zone_id'];
        $quarantine = $model->find($quarantineId);
        if (!$quarantine) {
            throw new BadRequestException('Не найден Карантин с переданным id');
        }

        if ((!$isStatistics && !$isActList)
            || $filter['district']) {
            $query->leftJoin('pets_to_owner pto', "pets.id = pto.id_pet")
                ->innerJoin('pet_owner_type pot', 'pto.id_owner_type = pot.id AND pot.is_owner = true')
                ->leftJoin('pet_owners ptopo', 'ptopo.id = pto.id_owner')
                ->innerJoin(FiasAddresses::tableName() . ' fa', 'fa.id = '
                    . 'CASE '
                    . 'WHEN pets.id_fias_address IS NOT NULL THEN pets.id_fias_address '
                    . 'WHEN ptopo.id_fact_fias_address IS NOT NULL THEN ptopo.id_fact_fias_address '
                    . 'WHEN ptopo.id_fias_address IS NOT NULL THEN ptopo.id_fias_address '
                    . 'END');
        }

        if (!$isStatistics && !$isActList) {
            $expression = self::prepareWithinExpression($quarantine);
            $query->andWhere($expression);

            $query->joinWith(['species']);
            $query->andWhere(['id_reg_expire_reason' => null]);
            $query->andWhere(['id_main_pet' => null]);
            $query->andWhere(['species.tech_name' => [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG]]);
        }

        if (isset($filter['district'])) {
            $data = (new EfspWrapper())->getDistricts(EfspWrapper::MOSCOW_FIAS_CODE);
            $bti_adm_area_code = '';
            foreach ($data['level2'] as $level2) {
                if ($level2['code'] === $filter['district']) {

                    foreach ($level2['level3'] as $i => $level3) {

                        if (isset($filter['area']) && $level3['code'] === $filter['area']) {
                            $bti_adm_area_code .= "'" . $level3['bti_city_area_code'] . "',";
                        } else {
                            $bti_adm_area_code .= "'" . $level3['bti_city_area_code'] . "',";
                        }
                    }
                }
            }

            $bti_adm_area_code = substr($bti_adm_area_code, 0, -1);
            $query->andWhere(new Expression("fa.bti_city_area_code && ARRAY[$bti_adm_area_code::varchar]"));
        }

        if (!empty($filter['pet_owner'])) {
            $query->joinWith('owner')
                ->andWhere([
                    'or',
                    ['pet_owners.id' => intval($filter['pet_owner'])],
                    ['pet_owners.fullname' => $filter['pet_owner']]
                ]);
        }

        if (!empty($filter['pet_owner_phone'])) {
            $query->joinWith('owner.phoneContacts')
                ->andWhere(['=', 'public.contacts.name', '+' . $filter['pet_owner_phone']]);
        }

        if (!empty($filter['pet'])) {
            $query->andWhere([
                'or',
                ['pets.id' => intval($filter['pet'])],
                ['pets.name' => $filter['pet']],
            ]);
        }

        if (!empty($filter['species'])) {
            $query->andWhere(['IN', 'pets.id_species', $filter['species']]);
        }

        if (!empty($filter['specialist_id'])) {
            $query->joinWith('visitDetour')
                ->andWhere(['visits.author' => $filter['specialist_id']]);
        }

        if ((!empty($filter['is_vaccinated']) || !empty($filter['vaccine_till']) || !$isStatistics)
            && !$isActList) {

            $alias = 'pv';
            $diseaseId = $quarantine->id_disease;
            $tableName = self::getTableName($diseaseId);
            $query->leftJoin("$tableName $alias", "$alias.id_pet = pets.id");
            $query->leftJoin('tmc.tmc as tmc', "$alias.id_vaccine = tmc.id")
                ->leftJoin('tmc.tmc_to_diseases as ttd', "ttd.id_tmc = tmc.id");

            if (!empty($filter['is_vaccinated'])) {
                $query->andWhere("ttd.id_disease = $diseaseId AND $alias.valid_until > NOW()");
            }
            if (!empty($filter['vaccine_till'])) {
                $filterVaccineUntilTill = $filter['vaccine_till'];
                $query->andWhere("ttd.id_disease = $diseaseId AND $alias.valid_until <= '$filterVaccineUntilTill'" .
                    " OR $alias.id is null");
            }
        }

        if (!empty($filter['vaccine_till'])) {
            $diseaseId = $quarantine->id_disease;
            $tableName = self::getTableName($diseaseId);
            $query->addSelect(['has_vaccine_outside_till' => self::buildSubQueryHasVaccineOutsideDate($tableName, $diseaseId, $filter['vaccine_till'])]);
        }

        // закоментил на показ
//        if (!empty($filter['quarantine_zone_id'])) {
//            $query->leftJoin('violation', 'violation.id_pet = pets.id');
//
//            $query->andWhere(
//
//                [
//                'AND',
////                    со слов аналитика - нет в документации
////                    ['violation.id_type' => 4],
//                ['IN', 'violation.state', Violation::ACTIVE_STATES],
//                ['violation.id_quarantine' => $filter['quarantine_zone_id']]
//                ,
//            ]
//        );
//        }

        if (!empty($filter['is_refused'])) {
            $query->leftJoin('violation', 'violation.id_pet = pets.id');
            $query->andWhere([
                'AND',
                ['violation.id_type' => 4],
                ['IN', 'violation.state', Violation::ACTIVE_STATES],
                ['violation.id_quarantine' => $filter['quarantine_zone_id']]
            ]);
        }

        if (!empty($filter['detour_from']) && $isStatistics && empty($filter['is_refused'])) {
            $query->joinWith('visits v');
            $query->andWhere([
                'OR',
                [
                    'AND',
                    ['>=', 'v.fact_start_dttm', $filter['detour_from']],
                    ['v.type' => Visits::TYPE_VISIT_VC_DETOUR],
                    ['v.id_quarantine' => $quarantineId]
                ],
                ['>=', 'qdnv.date', $filter['detour_from']]
            ]);
        }
        if (!empty($filter['detour_till']) && $isStatistics && empty($filter['is_refused'])) {
            $query->joinWith('visits v');
            $query->andWhere([
                'OR',
                [
                    'AND',
                    ['<=', 'v.fact_start_dttm', $filter['detour_till']],
                    ['v.type' => Visits::TYPE_VISIT_VC_DETOUR],
                    ['v.id_quarantine' => $quarantineId]
                ],
                ['<=', 'qdnv.date', $filter['detour_till']]
            ]);
        }

        if ($isStatistics && empty($filter['is_refused'])) {
            $query->leftJoin('quarantine_detour_non_visit qdnv', "qdnv.id_pet = pets.id AND qdnv.id_quarantine = $quarantineId");
            $absentPetTypes = $filter['why_not_available'] ?? QuarantineDetourNonVisit::absenceReasonsWithPets();
            $detourType = Visits::TYPE_VISIT_VC_DETOUR;
            $query->leftJoin('visits qv', "qv.id_quarantine = $quarantineId AND qv.type = '$detourType'")
                ->leftJoin('visit_pets qvp', "qvp.id_visit = qv.id")
                ->andWhere([
                    'OR',
                    new Expression('qvp.id_pet = pets.id'),
                    ['IN', 'qdnv.absent_pet_reason', $absentPetTypes],
                ]);
        }

        if ($isActList) {
            $detourType = Visits::TYPE_VISIT_VC_DETOUR;
            $query->leftJoin('visits qv', "qv.id_quarantine = $quarantineId AND qv.type = '$detourType'")
                ->leftJoin('visit_pets qvp', "qvp.id_visit = qv.id")
                ->andWhere(new Expression('qvp.id_pet = pets.id'));
        }

        $query = (new ActiveQuery(Pets::class))
            ->select('*')
            ->distinct()
            ->with(self::getIncludes($filter))
            ->from(['main_query' => $query]);
        if (!empty($filter['vaccine_till'])) {
            // Исключаем животных у которых срок действия вакцины за пределами даты фильтра
            $query->andWhere(['has_vaccine_outside_till' => null]);
        }

        return $query;
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return CommonList
     */
    public static function getAllOutOrganizations(int $page = 1, int $limit = 10, array $filter = []): CommonList
    {
        $query = OutsideOrg::find();

        if (isset($filter['name'])) {
            $query->andWhere(['ILIKE', 'name', $filter['name']]);
        }

        $outOrganizations = $query
            ->limit($limit)
            ->orderBy(['name' => SORT_ASC])
            ->offset(($page - 1) * $limit)
            ->asArray();

        return new CommonList(
            'out_organizations',
            $outOrganizations->all(),
            $outOrganizations->count(),
            $page,
            $limit
        );
    }

    /**
     * @param \app\models\db\Quarantine $quarantine
     * @return \yii\db\Expression|false
     */
    private static function prepareWithinExpression(Quarantine $quarantine)
    {
        if (empty($quarantine->localities)) {
            return false;
        }

        $areas = [];
        foreach ($quarantine->localities as $locality) {
            if (!empty($locality->coords)) {
                $areas[] = 'ST_Within(fa.coords, ST_GeomFromText(\'' . $locality->getGeometry() . '\'))';
            }
        }

        if (empty($areas)) {
            return false;
        }

        return new Expression(implode(' OR ', $areas));
    }

    public static function getAllForAct(array $filter = []): array
    {
        $filter = self::validateFilter($filter);

        $query = Pets::find()
            ->with(self::getIncludes($filter))
            ->where(['id_pet_tmp' => null])
        ;

        if (!empty($filter)) {
            $query = self::applyFilter($query, $filter, false, true);
        }

        $tmcs = [];
        $petOwners = [];

        $pets = $query->asArray()->all();
        foreach ($pets as $pet) {
            $pet = self::setFullNameToSpecialist($pet);
            foreach ($pet['visitDetour'] as $visitDetour) {
                if (isset($visitDetour['visitServiceTmcs'][0]['balance']['id'])) {
                    $visitServiceTmcs = $visitDetour['visitServiceTmcs'][0];
                    $key = $visitServiceTmcs['balance']['inventory_number'];
                    if (isset($tmcs[$key])) {
                        $countSmall = $tmcs[$key]['count_sizes'][Pets::SIZE_SMALL];
                        $countBig = $tmcs[$key]['count_sizes'][Pets::SIZE_BIG];
                        $specialists = $tmcs[$key]['specialists'];
                        $cats = $tmcs[$key]['species'][9];
                        $dogsSmall = $tmcs[$key]['species'][25][Pets::SIZE_SMALL];
                        $dogsBig = $tmcs[$key]['species'][25][Pets::SIZE_BIG];

                        $tmcUsed = $tmcs[$key]['total_count'] + $visitServiceTmcs['count'];
                        $tmcUtil = $tmcs[$key]['total_count_utilize'] + $visitServiceTmcs['count_utilize'];
                    } else {
                        $countSmall = 0;
                        $countBig = 0;
                        $specialists = [];
                        $cats = 0;
                        $dogsSmall = 0;
                        $dogsBig = 0;

                        $tmcUsed = $visitServiceTmcs['count'];
                        $tmcUtil = $visitServiceTmcs['count_utilize'];
                    }

                    // Исключим user из ответа оставив только fullname
                    $pet = self::setFullNameToSpecialist($pet);
                    $specialists[$pet['visitDetour'][0]['author']] = $pet['visitDetour'][0]['author_ref'];

                    if ($pet['owner']) {
                        $petOwners[$key][] = $pet['owner']['id'];
                    }

                    $tmcs[$key] = [
                        'petOwners'           => $petOwners[$key] ?? [],
                        'visitServiceTmcs'    => $visitServiceTmcs,
                        'dosage'              => $visitServiceTmcs['dosage'],
                        'measure'             => $visitServiceTmcs['balance']['tmc']['measure'],
                        'balance'             => $visitServiceTmcs['balance'],
                        'tmc'                 => $visitServiceTmcs['tmc'],
                        'count_sizes'         => [
                            Pets::SIZE_SMALL => ($pet['visitDetour'][0]['pets'][0]['size_id'] === Pets::SIZE_SMALL ? ++$countSmall : $countSmall),
                            Pets::SIZE_BIG   => ($pet['visitDetour'][0]['pets'][0]['size_id'] === Pets::SIZE_BIG ? ++$countBig : $countBig),
                        ],
                        'species'             => [
                            '9'  => ($pet['visitDetour'][0]['pets'][0]['id_species'] === 9 ? ++$cats : $cats),
                            '25' => [
                                Pets::SIZE_SMALL => ($pet['visitDetour'][0]['pets'][0]['id_species'] === 25 && $pet['visitDetour'][0]['pets'][0]['size_id'] === Pets::SIZE_SMALL ? ++$dogsSmall : $dogsSmall),
                                Pets::SIZE_BIG   => ($pet['visitDetour'][0]['pets'][0]['id_species'] === 25 && $pet['visitDetour'][0]['pets'][0]['size_id'] === Pets::SIZE_BIG ? ++$dogsBig : $dogsBig),
                            ],
                        ],
                        'specialists'         => $specialists,
                        'total_count'         => number_format($tmcUsed, 2),
                        'total_count_utilize' => number_format($tmcUtil, 2),
                    ];
                }
            }
        }

        return $tmcs;
    }

    /**
     * @param $query
     * @param $quarantineDiseaseId
     * @param $alias
     * @return mixed
     */
    private static function getTableName($quarantineDiseaseId)
    {
        $rabiesId = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one()->id;
        switch ($quarantineDiseaseId) {
            case $rabiesId:
                $tableName = PetRabiesVaccination::tableName();
                break;
            default:
                $tableName = PetOtherVaccinations::tableName();
        }

        return $tableName;
    }

    /**
     * @param $disease_id
     * @param $date
     * @return Query
     */
    private static function buildSubQueryHasVaccineOutsideDate($tableName, $disease_id, $date): Query
    {
        return (new Query())
            ->select(new Expression(' true '))
            ->from($tableName)
            ->leftJoin('tmc.tmc as tmc', "$tableName.id_vaccine = tmc.id")
            ->leftJoin('tmc.tmc_to_diseases as ttd', 'ttd.id_tmc = tmc.id')
            ->where([
                'AND',
                ['ttd.id_disease' => $disease_id],
                new Expression("$tableName.valid_until > '$date'"),
                new Expression("pets.id = $tableName.id_pet")
            ])
            ->limit(1);
    }

    /**
     * @param $page
     * @param $limit
     * @param $filter
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public static function getNonExistedPets($page, $limit, $filter)
    {
        $filter = self::validateFilter($filter);

        $query = QuarantineDetourNonVisit::find()
            ->with([
                'pet',
                'fias_address'
            ]);

        if (!empty($filter)) {
            $query = self::applyNonExistedFilter($query, $filter);
        }

        $nonExistedPets = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->asArray();

        return new CommonList(
            'pets',
            $nonExistedPets->all(),
            $nonExistedPets->count(),
            $page,
            $limit
        );
    }

    /**
     * @param int $pet_id
     * @param int $owner_id
     * @param int $size_id
     * @param int $species_id
     * @param string $sex
     * @param int $breed_id
     * @param string $birthday
     * @param string $description
     * @param array $contacts
     * @param array $fact_fias_address
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function updatePetAndOwnerData(
        int $pet_id,
        int $owner_id,
        array $contacts,
        array $fact_fias_address,
        array $pet_identification,
        string $description = null,
        string $birthday = null,
        int $species_id = null,
        int $size_id = null,
        int $breed_id = null,
        string $sex = null
    ) {
        // Pet
        $pet = Pets::findOne($pet_id);
        $pet->size_id = $size_id;
        $pet->id_species = $species_id;
        $pet->sex = $sex;
        $pet->id_breed = $breed_id;
        $pet->birthday = $birthday;
        $pet->description = $description;
        $pet->save();

        // Pet - Pet Identification
        (new IdentModel)->save($pet_id, $pet_identification);

        // Pet Owner - Fact FIAS Address
        $petOwner = $pet->owner;
        if (!$petOwner) {
            $petOwner = PetOwners::findOne($owner_id);
        }
        if ($petOwner) {
            if (!empty($fact_fias_address)) {
                $petOwner->id_fact_fias_address = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);
                if (!$petOwner->id_area) {
                    $petOwner->id_area = FiasAddresses::findOne($petOwner->id_fact_fias_address)->id_area;
                }
                if (!$petOwner->id_district) {
                    $petOwner->id_district = FiasAddresses::findOne($petOwner->id_fact_fias_address)->id_district;
                }
                $petOwner->save();
            } else {
                $petOwner->id_fact_fias_address = null;
            }
        }

        // Pet Owner - Contact
        foreach ($contacts as $contact) {
            $oldContact = Contacts::findOne([
                'id_contact_type' => $contact['id_contact_type'],
                'entity_type'     => $contact['entity_type'],
                'entity_id'       => $contact['entity_id'],
                'name'            => $contact['name'],
            ]);
            if (!$oldContact) {
                (new ContactsModel())->create(
                    $contact['id_contact_type'],
                    $contact['entity_type'],
                    $contact['entity_id'],
                    $contact['name'],
                    $contact['main_flag']
                );
            }
        }
    }

    /**
     * @param int $owner_id
     * @param int|null $pet_id
     * @return bool
     */
    public static function manageOwnerIsMoved(int $owner_id, int $pet_id = null): bool
    {
        /*
         * Владелец животного съехал - удаляем адрес, по которому было найдено животное
         * 1) Адрес содержания животного (Pets->fias_address)
         * 2) Адрес фактического проживания владельца (Owner->fact_fias_address)
         * 3) Адрес прописки владельца (Owner->fias_address)
         */
        if ($pet_id) {
            $pet = Pets::findOne($pet_id);
            $owner = $pet->owner;
            $addressToDelete = self::getLocatedAddress($pet);

            $pet->id_fias_address = $pet->id_fias_address === $addressToDelete->id ? null : $pet->id_fias_address;
            $pet->save();
        } else {
            $owner = PetOwners::findOne($owner_id);
            $addressToDelete = self::getLocatedAddress($owner);
        }
        $owner->id_fact_fias_address = $owner->id_fact_fias_address === $addressToDelete->id ? null : $owner->id_fact_fias_address;
        $owner->id_fias_address = $owner->id_fias_address === $addressToDelete->id ? null : $owner->id_fias_address;

        return $owner->save();
    }

    /**
     * @param int $quarantine_zone_id
     * @param int $why_not_available
     * @param string $detourDate
     * @param array $fact_fias_address
     * @param int $pet_id
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function manageWhyNotAvailable(int $quarantine_zone_id, int $why_not_available, string $detourDate, array $fact_fias_address, int $pet_id)
    {
        /*
         * Если животное отсутствует - запишем инфу об этом в QuarantineDetourNonVisit для статистики
         */
        $fiasAddressId = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);

        $quarantineDetour = (new QuarantineDetourNonVisit());
        $quarantineDetour->id_quarantine = $quarantine_zone_id;
        $quarantineDetour->date = $detourDate;
        $quarantineDetour->id_fias_address = $fiasAddressId;
        $quarantineDetour->absent_pet_reason = $why_not_available;
        $quarantineDetour->id_pet = $pet_id;
        $quarantineDetour->save();

        if (!$quarantineDetour->save()) {
            $errors = $quarantineDetour->getErrorSummary(true);
            throw new Exception(empty($errors) ? 'Ошибка при сохранении данных обхода' : implode("\n", array_values($errors)));
        }

        /*
         * Если животное умерло или "фиктивное" - снимаем его с учёта
         */
        if ($why_not_available === FlatModel::PET_IS_DEATH
            || $why_not_available === FlatModel::PET_NOT_EXISTS) {
            $reasonTechName = $why_not_available === FlatModel::PET_IS_DEATH ? RegExpireReasons::TECH_NAME_DEATH : RegExpireReasons::TECH_NAME_NOT_EXIST;
            /** @var RegExpireReasons $reason */
            $reason = RegExpireReasons::find()->where(['tech_name' => $reasonTechName])->one();
            if (!$reason) {
                throw new Exception("Не найдена причина снятия с учёта типа: $reasonTechName");
            }

            /** @var Pets $pet */
            $pet = Pets::find()->where(['id' => $pet_id])->one();
            $pet->reg_expire_date = date('Y-m-d');
            $pet->id_reg_expire_reason = $reason->id;

            if (!$pet->save()) {
                $errors = $pet->getErrorSummary(true);
                throw new Exception(empty($errors) ? 'Ошибка при снятии животного с учёта' : implode("\n", array_values($errors)));
            }
        }

        /*
         * Текущий владелец является представителем, а не основным владельцем
         * Изменим тип текущего владельца на "представителя"
         */
        if ($why_not_available === FlatModel::PET_HAS_OTHER_OWNER) {
            /** @var Pets $pet */
            $pet = Pets::find()->where(['id' => $pet_id])->one();
            if (!$owner = $pet->owner) {
                throw new Exception('У животного не найдено владельца');
            }
            /** @var PetsToOwner $petsToOwner */
            $petsToOwner = PetsToOwner::find()->where(['id_pet' => $pet_id, 'id_owner' => $owner->id])->one();

            /** @var PetOwnerType $ownerTypeRepresentative */
            $ownerTypeRepresentative = PetOwnerType::find()->where(['is_owner' => false])->one();

            (new PetToOwnerModel())->edit($petsToOwner->id, $ownerTypeRepresentative->id, $owner->id);
        }

        return true;
    }

    /**
     * @param int $quarantine_zone_id
     * @param int $owner_id
     * @param int $pet_id
     * @param Visits $visit
     * @param string $rejected_info
     * @param array $fact_fias_address
     * @param string $detourDate
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function manageVaccineRejection(int $quarantine_zone_id, int $owner_id, int $pet_id, Visits $visit, string $rejected_info, array $fact_fias_address, string $detourDate)
    {
        /** @var Quarantine $quarantine */
        $quarantine = Quarantine::find()->where(['id' => $quarantine_zone_id])->one();
        /** @var ViolationType $violationType */
        $violationType = ViolationType::find()->where(['tech_name' => ViolationType::V04_VACCINATION_REJECTION])->one();

        // Фронт повторно отсылает старое нарушение из предзаполненой формы, поэтому не проводим проверку
        //на повторное нарушение в этих кейсах и не создаём новое нарушение
        /** @var Violation $violation */
        $quarantineViolation = Violation::find()->where([
            'AND',
            ['id_type' => $violationType->id_type],
            ['id_pet' => $pet_id],
            ['IN', 'state', Violation::ACTIVE_STATES],
            ['id_visit' => $visit->id],
            ['rejection_reason' => $rejected_info],
        ])->one();

        if (!$quarantineViolation) {
            $violation = new Violation([
                'state'            => Violation::STATE_ACCEPTED,
                'id_owner'         => $owner_id,
                'id_pet'           => $pet_id,
                'id_type'          => $violationType->id_type,
                'id_disease'       => $quarantine->id_disease,
                'id_visit'         => $visit->getPrimaryKey(),
                'date_violation'   => new Expression('now()'),
                'rejection_reason' => $rejected_info,
                'id_quarantine'    => $quarantine_zone_id,
            ]);

            /*
             * Такое же есть?
             */
            if ($violation->isPetHasActiveViolation()) {
                throw new Exception('На это животное уже заведено такое же нарушение');
            }
        } else {
            $violation = $quarantineViolation;
        }

        if (!$violation->save()) {
            $errors = $violation->getErrorSummary(true);
            throw new Exception(empty($errors) ? 'Ошибка при сохранении данных обхода' : implode("\n", array_values($errors)));
        }

        $fiasAddressId = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);

        $quarantineDetour = (new QuarantineDetourNonVisit());
        $quarantineDetour->id_quarantine = $quarantine_zone_id;
        $quarantineDetour->date = $detourDate;
        $quarantineDetour->id_fias_address = $fiasAddressId;
        $quarantineDetour->absent_pet_reason = FlatModel::OWNER_REFUSED;
        $quarantineDetour->id_pet = $pet_id;

        if (!$quarantineDetour->save()) {
            $errors = $quarantineDetour->getErrorSummary(true);
            throw new Exception(empty($errors) ? 'Ошибка при сохранении данных обхода' : implode("\n", array_values($errors)));
        }
    }

    /**
     * @param int|null $balance_id
     * @param int|null $specialist_id
     * @return null
     * @throws NotFoundHttpException
     */
    public static function getBalance(int $balance_id = null, int $specialist_id = null)
    {
        if ($balance_id) {
            $balance = Balance::findOne([
                'id'            => $balance_id,
                'id_specialist' => $specialist_id,
            ]);
            if (!$balance) {
                throw new NotFoundHttpException('Не хватает вакцины на балансе.');
            }

            return $balance;
        }

        return null;
    }

    /**
     * @param int $vaccine_id
     * @param Balance|null $balance
     * @return TmcVaccine|null
     */
    public static function getTmc(int $vaccine_id, Balance $balance = null)
    {
        if ($balance) {
            return TmcVaccine::findOne($balance->id_tmc);
        } else {
            return TmcVaccine::findOne($vaccine_id);
        }
    }

    /**
     * @param TmcBase $tmcVaccine
     * @param int $pet_id
     * @param string $vaccine_series
     * @param string $vaccine_best_before
     * @param Visits $visit
     * @param Balance|null $balance
     * @param Dosages|null $dosage
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function addTmcVaccine(
        TmcBase $tmcVaccine,
        int $pet_id,
        string $vaccine_series,
        string $vaccine_best_before,
        string $new_valid_until,
        Visits $visit,
        Balance $balance = null,
        Dosages $dosage = null
    ) {
        $balanceTmcs = [];
        $otherTmcs = [];
        if ($balance) {
            $balanceTmcs = [
                [
                    'row_id'                => 1,
                    'id_balance_tmc'        => $balance->id,
                    'id_tmc'                => $tmcVaccine->id,
                    'type_tmc'              => TmcBase::TYPE_VACCINE,
                    'count_selected'        => 1,
                    'count_production_form' => floatval($dosage->dosage / $balance->production_form->volume),
                    'id_dosage'             => $dosage->id,
                    'write_off_pack_form'   => $balance->production_form->is_utilize,
                    'pets'                  => [
                        $pet_id,
                    ],
                    'valid_until'           => $new_valid_until ?? date('Y-m-d', strtotime($visit->fact_start_dttm . ' +1 year')),
                ],
            ];
        } else {
            $otherTmcs = [
                [
                    'row_id'          => 1,
                    'id_tmc'          => $tmcVaccine->id,
                    'type_tmc'        => TmcBase::TYPE_VACCINE,
                    'count_selected'  => 1,
                    'id_dosage'       => $dosage->id ?? null,
                    'batch'           => $vaccine_series,
                    'production_date' => null,
                    'expiry_date'     => $vaccine_best_before,
                    'pets'            => [
                        $pet_id,
                    ],
                    'valid_until'     => $new_valid_until ?? date('Y-m-d', strtotime($visit->fact_start_dttm . ' +1 year')),
                ],
            ];
        }

        (new ServiceTmcsModelSave())->save(
            $visit->id,
            $visit->visitsGovServices[0]->id,
            $balanceTmcs,
            $otherTmcs,
            $new_valid_until
        );
    }

    /**
     * @param int $quarantine_zone_id
     * @param int $pet_id
     * @param int $vaccine_id
     * @param TmcBase $tmcVaccine
     * @param string $detourDate
     * @param int $id_organization
     * @param int $id_specialist
     * @param Visits $visit
     * @param string $vaccine_best_before
     * @param string $vaccine_series
     * @param array $fact_fias_address
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function addOutsideVaccine(
        int $quarantine_zone_id,
        int $pet_id,
        int $vaccine_id,
        TmcBase $tmcVaccine,
        string $detourDate,
        int $id_organization,
        int $id_specialist,
        Visits $visit,
        string $vaccine_best_before,
        string $vaccine_series,
        array $fact_fias_address,
        string $new_valid_until
    ) {
        $vaccinationModel = (new VaccinationModel());
        $vaccines = $vaccinationModel->getAll($pet_id);
        $vaccine = [
            'id_vaccine'      => $vaccine_id,
            'type_tmc'        => TmcBase::TYPE_VACCINE,
            'drug_name'       => $tmcVaccine->name,
            'producer_name'   => $tmcVaccine->produced,
            'date'            => $detourDate,
            'id_organization' => $id_organization,
            'is_out_org'      => true,
            'id_specialist'   => $id_specialist,
            'valid_until'     => $new_valid_until ?? date('Y-m-d', strtotime($visit->fact_start_dttm . ' +1 year')),
            'expiry_date'     => $vaccine_best_before,
            'batch'           => $vaccine_series,
        ];
        /** @var TmcVaccine $tmcVaccine */
        $tmcVaccine = TmcVaccine::find()->where(['id' => $vaccine_id])->one();
        $tableName = $tmcVaccine->isDiseasesRabies() ? VaccinationModel::ATTR_PET_RABIES_VACCINATION : VaccinationModel::ATTR_PET_OTHER_VACCINATIONS;
        array_push($vaccines[$tableName], $vaccine);
        $vaccinationModel->save($pet_id, $vaccines);

        $fiasAddressId = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);

        $quarantineDetour = (new QuarantineDetourNonVisit());
        $quarantineDetour->id_quarantine = $quarantine_zone_id;
        $quarantineDetour->date = $detourDate;
        $quarantineDetour->id_fias_address = $fiasAddressId;
        $quarantineDetour->absent_pet_reason = FlatModel::PET_HAS_OUTSIDE_ORG_VACCINE;
        $quarantineDetour->id_pet = $pet_id;

        if (!$quarantineDetour->save()) {
            $errors = $quarantineDetour->getErrorSummary(true);
            throw new Exception(empty($errors) ? 'Ошибка при сохранении данных обхода' : implode("\n", array_values($errors)));
        }
    }

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    private static function applyNonExistedFilter($query, $filter)
    {
        if (!empty($filter['quarantine_zone_id'])) {
            $query->andWhere(['id_quarantine' => $filter['quarantine_zone_id']]);
            $query->andWhere(['IN', 'absent_pet_reason', FlatModel::LIVING_SPACE_HAS_NO_PET]);
        }

        if (!empty($filter['detour_from'])) {
            $dateFrom = $filter['detour_from'];
            $query->andWhere(new Expression("quarantine_detour_non_visit.date >= $dateFrom"));
        }
        if (!empty($filter['detour_till'])) {
            $dateTill = $filter['detour_till'];
            $query->andWhere(new Expression("quarantine_detour_non_visit.date <= $dateTill"));
        }
        if (!empty($filter['address'])) {
            $query->andWhere(['ILIKE', 'full_address', $filter['address']]);
        }

        return $query;
    }

    /**
     * Дёрнем fullname из user и уберём лишние данные user из ответа
     *
     * @param array $pet
     * @return array
     */
    private static function setFullNameToSpecialist(array $pet)
    {
        foreach ($pet['visitDetour'] as &$visitDetour) {
            if (isset($visitDetour['author_ref']) && isset($visitDetour['author_ref']['user'])) {
                $visitDetour['author_ref']['fullname'] = $visitDetour['author_ref']['user']['fullname'];
                unset($visitDetour['author_ref']['user']);
            }
        }

        return $pet;
    }

    /**
     * @param Pets|PetOwners $entity
     * @return FiasAddresses|null
     */
    private static function getLocatedAddress($entity)
    {
        if ($entity instanceof Pets && $entity->fias_address) {
            return $entity->fias_address;
        }

        $owner = $entity instanceof Pets ? $entity->owner : $entity;
        if ($address = $owner->fact_fias_addresses) {
            return $address;
        }
        if ($address = $owner->fias_addresses) {
            return $address;
        }

        return null;
    }
}
