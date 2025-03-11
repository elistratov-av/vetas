<?php

namespace app\modules\v2\modules\vaccinationJournal\models;

use app\common\validators\FullTrimValidator;
use app\models\db\Diseases;
use app\models\db\Organizations;
use app\models\db\Pets;
use app\models\db\ShelterGuests;
use app\models\db\Violation;
use app\models\db\Visits;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * Class StationModel
 *
 * @package app\modules\v2\modules\vaccinationJournal\models
 */
class ShelterModel
{
    /**
     * @param array $filter
     * @return array
     */
    private static function getIncludes(array $filter = []): array
    {
        $includes = [
            'shelter_records',
            'pet_identification',
            'violations',
            'breeds',
            'species',
            'pet_ectoparasites',
            'pet_other_vaccinations',
            'pet_rabies_vaccinations',
            'quarantineFocus',
            'quarantineFocus.quarantine',
            'visitShelter.pets',
            'visitShelter.pets.pets_to_owner',
            'visitShelter.pets.pets_to_owner.owner_type',
            'visitShelter.pets.pets_to_owner.owner',
            'visitShelter.services',
            'visitShelter.specialists',
            'visitShelter.author_ref',
            'visitShelter.author_ref.organization',
            'visitShelter.author_ref.user',
            'visitShelter.visitServiceTmcs',
            'visitShelter.visitServiceTmcs.balance',
            'visitShelter.visitServiceTmcs.balance.organization',
            'visitShelter.visitServiceTmcs.balance.production_form',
            'visitShelter.visitServiceTmcs.balance.tmc',
            'visitShelter.visitServiceTmcs.balance.tmc.measure',
            'visitShelter.visitServiceTmcs.tmc',
            'visitShelter.visitServiceTmcs.dosage',
            'visitShelter.visitServiceTmcs.dosage.measure',
            'visitShelter.visitServiceTmcPet',
            'owner',
            'owner.fact_fias_addresses',
            'owner.fias_addresses',
            'owner.contacts',
            'owner.phoneContacts',
            'owner.emailContacts',
        ];

        if (!empty($filter['shelter_id'])) {
            $includes['visitShelter'] = function (ActiveQuery $query) use ($filter) {
                $shelter_id = $filter['shelter_id'];

                $query
                    ->leftJoin('shelter_guests', "shelter_guests.id_organization = $shelter_id")
                    ->andWhere(
                        '(visits.fact_start_dttm >= shelter_guests.arrival_date AND visits.fact_start_dttm <= shelter_guests.departure_date) '.
                        'OR (visits.fact_start_dttm >= shelter_guests.arrival_date AND shelter_guests.departure_date IS NULL)'
                    )
                    ->orderBy('fact_start_dttm desc');

                if (!empty($filter['vaccination_from']) || !empty($filter['vaccination_till'])) {
                    $query->leftJoin('visit_service_tmc vst', 'visits.id = vst.id_visit');
                }
                if (!empty($filter['vaccination_from']))
                    $query->andWhere(['>=', 'visits.fact_start_dttm', $filter['vaccination_from']])
                        ->andWhere(new Expression('vst.id is not null'));
                if (!empty($filter['vaccination_till']))
                    $query->andWhere(['<=', 'visits.fact_start_dttm', $filter['vaccination_till'] . ' 23:59:59'])
                        ->andWhere(new Expression('vst.id is not null'));
            };
        }

        $includes['shelterRejections'] = function (ActiveQuery $query) use ($filter) {
            $query->orderBy('created_at desc');

            if (!empty($filter['shelter_id']))
                $query->andWhere(['id_organization' => $filter['shelter_id']]);
            if (!empty($filter['vaccination_from']))
                $query->andWhere(['>=', 'created_at', $filter['vaccination_from']]);
            if (!empty($filter['vaccination_till']))
                $query->andWhere(['<=', 'created_at', $filter['vaccination_till'] . ' 23:59:59']);
        };

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
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     * @param bool  $isStatistics
     *
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

        $prvQuery = (new \yii\db\Query())
            ->select(['id_pet', new Expression('MAX(valid_until) AS valid_until'), new Expression('MAX(date) AS date')])
            ->from('pet_rabies_vaccination');

        if (!empty($filter['vaccine'])) {
            $prvQuery->where(['id_vaccine' => $filter['vaccine']]);
        }

        $prvQuery->groupBy(['id_pet']);

        $subQuery = ShelterGuests::find()
            ->select([
                'id_pet' => 'pets.id',
                'id_breed',
                'breed'=>'breeds.name',
                'species'=>'species.name',
                'pet_identification'=>'pet_identification.identification_code',
                'id_species',
                'color' => 'pet_ref_color.title',
                'birthday' => 'pets.birthday',
                'name' => 'pets.name',
                'sex',
                'size_id' => 'pets.size_id',
                'description' => 'pets.description',
                'arrival_date' => 'shelter_guests.arrival_date',
                'rejection_reason_date' => 'shelter_vaccine_rejection.updated_at',
                'rejection_reason' => 'shelter_vaccine_rejection.description',
                'id_violation' => 'violation.id_type',
                'valid_until',
                'date',
                new Expression('ROW_NUMBER() OVER (PARTITION BY pets.id ORDER BY shelter_guests.arrival_date DESC) AS rn')
            ])
            ->leftJoin('pets', 'shelter_guests.id_pet = pets.id')
            ->leftJoin('pet_ref_color', 'pets.color_id = pet_ref_color.id')
            ->leftJoin('pet_identification', 'pet_identification.id_pet = pets.id AND pet_identification.id_ident_type = 1 ')
            ->leftJoin('breeds', 'breeds.id = pets.id_breed')
            ->leftJoin('species', 'species.id = pets.id_species')
            ->leftJoin('shelter_vaccine_rejection', 'shelter_vaccine_rejection.id_pet = pets.id')
            ->leftJoin('violation', 'violation.id_pet = pets.id AND violation.id_type = 4');

        $subQuery->where([
            'and',
            ['not in', 'shelter_guests.status', ['DEPARTURED']],
            ['is not', 'pets.name', null],
            ['<>', 'pets.name', ''],
            ['is not', 'pets.id', null],
        ]);

        if (!empty($filter['vaccination']) && $filter['vaccination'] == 'vaccination') {
            $subQuery->andWhere('latest_vaccination.valid_until > NOW()');
            $subQuery->innerJoin(['latest_vaccination' => $prvQuery], 'latest_vaccination.id_pet = pets.id');

        } elseif (!empty($filter['vaccination']) && $filter['vaccination'] == 'not_vaccination') {
            $subQuery->andWhere('(latest_vaccination.valid_until IS NULL OR latest_vaccination.valid_until < NOW())');
            $subQuery->leftJoin(['latest_vaccination' => $prvQuery], 'latest_vaccination.id_pet = pets.id');

        } elseif (!empty($filter['vaccination']) && $filter['vaccination'] == 'vaccination_be_ended') {
            $subQuery->andWhere('(valid_until >= NOW() AND latest_vaccination.valid_until <= NOW()+INTERVAL \'30 DAY\')');
            $subQuery->innerJoin(['latest_vaccination' => $prvQuery], 'latest_vaccination.id_pet = pets.id');
        } else {
            if (!empty($filter['vaccine'])) {
                $subQuery->innerJoin(['latest_vaccination' => $prvQuery], 'latest_vaccination.id_pet = pets.id');
            }else{
                $subQuery->leftJoin(['latest_vaccination' => $prvQuery], 'latest_vaccination.id_pet = pets.id');
            }
        }

        if (!empty($filter)) {
            $subQuery = self::applyFilter($subQuery, $filter, $isStatistics);
        }

        $query = (new \yii\db\Query())
            ->from(['ranked_pets' => $subQuery])
            ->where(['rn' => 1]);

        $pets_count = clone($query);

        $all_pets = $pets_count->all();

        $pets = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->all();

        foreach ($pets as $index => &$pet) {
            $pet['id'] = $pet['id_pet'];
        }
        unset($pet);

        $pets_count_vaccination_ending= 0;
        $pets_count_not_vaccinated = 0;

        $date = new \DateTime();
        $today_date = new \DateTime($date->format('Y-m-d') . '+0 day');
        $vac_date_30 = new \DateTime($date->format('Y-m-d') . '+30 day');

        foreach ($all_pets as $pet) {
            $vac_date = new \DateTime($pet['valid_until']);

            if (empty($pet['valid_until']) || $pet['valid_until'] == '' || $vac_date < $today_date) {
                $pets_count_not_vaccinated++;
            } else {
                if ($vac_date >= $today_date && $vac_date <= $vac_date_30) {
                    $pets_count_vaccination_ending++;
                }
            }
        }

        $pets_counter = count($all_pets);
        $pets_count_vaccinated =  $pets_counter - $pets_count_not_vaccinated;

        $extra_data =
            [
                'pets_count_vaccinated' => $pets_count_vaccinated,
                'pets_count_vaccination_ending' => $pets_count_vaccination_ending,
                'pets_count_not_vaccinated' => $pets_count_not_vaccinated
            ];

        return new CommonList(
            'pets',
            $pets,
            $pets_counter,
            $page,
            $limit,
            $extra_data
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
            $pet = self::setFullNameToSpecialist($pet);
        }

        return $pets;
    }

    /**
     * @param array $filter
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    private static function validateFilter(array $filter): array
    {
        $emptyFilter = [
            'shelter_id' => null,
            'vaccination_from' => null,
            'vaccination_till' => null,
            'birthday_from' => null,
            'birthday_till' => null,
            'arrived_from' => null,
            'arrived_till' => null,
            'vaccine_until_from' => null,
            'vaccine_until_till' => null,
            'is_vaccinated' => null,
            'is_refusal_to_vaccinate' => null,
            'pet' => null,
            'pet_species_ids' => null,
            'specialist_id' => null,
            'identification' => null,
            'orgs_ids' => null,
        ];
        $filter = array_merge($emptyFilter, $filter);

        $rules = [
            [['shelter_id'], 'required'],
            [['shelter_id', 'specialist_id'], 'integer'],
            [['pet'], 'string'],
            [['pet'], FullTrimValidator::class],
            [['identification'], 'each', 'rule' => [FullTrimValidator::class]],
            [['is_vaccinated', 'is_refusal_to_vaccinate'], 'boolean'],
            [['pet_species_ids', 'orgs_ids'], 'each', 'rule' => ['integer']],
            [
                [
                    'vaccination_from',
                    'vaccination_till',
                    'birthday_from',
                    'birthday_till',
                    'arrived_from',
                    'arrived_till',
                    'vaccine_until_from',
                    'vaccine_until_till',
                ],
                'date',
                'format' => 'php:Y-m-d'
            ],
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
     * @param array       $filter
     * @param bool        $isStatistics
     *
     * @return ActiveQuery
     */
    private static function applyFilter(ActiveQuery $query, array $filter, bool $isStatistics = false): ActiveQuery
    {
        if (!empty($filter['shelter_id'])) {
            $query->andWhere(['shelter_guests.id_organization' => $filter['shelter_id'] ]);
        } else {
            $query->andWhere('1 != 1');
        }

        if (!empty($filter['vaccination_from'])) {
            $query->andWhere(['>=', 'date', $filter['vaccination_from']]);
        }
        if (!empty($filter['vaccination_till'])) {
            $query->andWhere(['<=', 'date', $filter['vaccination_till'] ]);
        }

        if (!empty($filter['birthday_from']) && !empty($filter['birthday_till'])) {
            $query->andWhere([
                'BETWEEN',
                'pets.birthday',
                $filter['birthday_from'],
                $filter['birthday_till'],
            ]);
        } elseif (!empty($filter['birthday_from'])) {
            $query->andWhere([
                '>=',
                'pets.birthday',
                $filter['birthday_from'],
            ]);
        } elseif (!empty($filter['birthday_till'])) {
            $query->andWhere([
                '<=',
                'pets.birthday',
                $filter['birthday_till'],
            ]);
        }

        if (!empty($filter['pet'])) {
            $query->andWhere([
                'or',
                ['pets.id' => intval($filter['pet'])],
                ['pets.name' => $filter['pet']],
            ]);
        }

        if (!empty($filter['pet_species_ids'])) {
            $query->andWhere(['IN', 'pets.id_species', $filter['pet_species_ids']]);
        }

        if (!empty($filter['specialist_id'])) {
            $query->joinWith('visitShelter')
                ->andWhere(['visits.author' => $filter['specialist_id']]);
        }

        if (!empty($filter['identification'])) {
            $query->andWhere(['IN', 'pet_identification.identification_code', $filter['identification']]);
        }

        if (!empty($filter['arrived_from'])) {
            $query->andWhere(['>=', 'shelter_guests.arrival_date', $filter['arrived_from']]);
        }

        if (!empty($filter['arrived_till'])) {
            $query->andWhere(['<=', 'shelter_guests.arrival_date', $filter['arrived_till']]);
        }

        if (!empty($filter['orgs_ids'])) {
            $query->leftJoin(
                'pet_rabies_vaccination as guest_prv',
                'guest_prv.id_pet = pets.id '.
                'AND (guest_prv.date >= arrival_date AND guest_prv.date <= departure_date '.
                'OR guest_prv.date >= arrival_date AND departure_date IS NULL)')
                ->andWhere(['IN', 'guest_prv.id_organization', $filter['orgs_ids']])
            ;
        }

        if (!empty($filter['vaccine_until_from'])) {
            $dateFrom = $filter['vaccine_until_from'];
            $query->leftJoin('pet_rabies_vaccination prv_in_range',
                'prv_in_range.id_pet = pets.id '.
                "AND prv_in_range.valid_until >= '$dateFrom'");
        }

        if (!empty($filter['vaccine_until_till'])) {
            $diseaseId = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one()->id;
            $query->addSelect(['has_vaccine_outside_till' => self::buildSubQueryHasVaccineOutsideDate($diseaseId, $filter['vaccine_until_till'])]);
        }

        if (!empty($filter['is_refusal_to_vaccinate'])) {
            $query->andWhere(['AND',
                ['violation.id_type' => 4],
                ['IN', 'violation.state', Violation::ACTIVE_STATES]
            ]);
        }

        if (!$isStatistics && !$filter['show_all']) {
            // Если есть фильтр "Дата окончания срока действия вакцинации до" фильтруем действующие вакцины по нему
            // в противном случаем истекающие в течении 30 дней
            $filterVaccineUntilTill = !empty($filter['vaccine_until_till']) ? $filter['vaccine_until_till'] : null;
            $valid_until = $filterVaccineUntilTill ? "'$filterVaccineUntilTill'" : 'NOW()::DATE + 30';
            $diseaseId = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one()->id;
            $query->leftJoin('pet_rabies_vaccination as prv', "prv.id_pet = pets.id AND prv.valid_until > NOW()")
                ->leftJoin('tmc.tmc as tmc', 'prv.id_vaccine = tmc.id')
                ->leftJoin('tmc.tmc_to_diseases as ttd', "ttd.id_tmc = tmc.id")
                ->andWhere("ttd.id_disease = $diseaseId AND prv.valid_until <= $valid_until ".
                    'OR prv.id is NULL')

                ->andWhere('departure_date is null OR departure_date > now()');
        }

        if ($isStatistics) {
            // Подготовим запрос по осмотрам
            $andWhere = ['OR',
                ['AND',
                    new Expression('visits.fact_start_dttm >= shelter_guests.arrival_date'),
                    new Expression('visits.fact_start_dttm <= shelter_guests.departure_date'),
                ],
                ['AND',
                    new Expression('visits.fact_start_dttm >= shelter_guests.arrival_date'),
                    ['shelter_guests.departure_date' => null],
                ]
            ];

//             Если нет фильтров по вакцинам, то так же включаем записи по отказам
            if (empty($filter['vaccination_from']) && empty($filter['vaccination_till'])) {
                array_push($andWhere, ['shelter_vaccine_rejection.id_organization' => $filter['shelter_id']]);
            }

            $shelter = Visits::TYPE_VISIT_VC_SHELTER;
            $query->leftJoin('visit_pets vp', 'vp.id_pet = pets.id')
                ->leftJoin('visits', "vp.id_visit = visits.id AND visits.type = '$shelter'")
                ->joinWith('shelterRejections')
                ->andWhere($andWhere);
        }

        $query = (new ActiveQuery(Pets::class))
            ->select('*')
            ->distinct()
            ->with(self::getIncludes($filter))
            ->from(['main_query' => $query]);
        if (!empty($filter['vaccine_until_till'])) {
            // Исключаем животных у которых срок действия вакцины за пределами даты фильтра
            $query->andWhere(['has_vaccine_outside_till' => null]);
        }
        return $query;
    }

    /**
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     *
     * @return CommonList
     */
    public static function getAllShelters(int $page = 1, int $limit = 10, array $filter = []): CommonList
    {
        $query = Organizations::find()
            ->joinWith([
                'org_type' => function ($query) {
                    $query->where(['org_types.is_tech' => true]);
                },
            ]);

        if (isset($filter['name'])) {
            $query->andWhere(['ILIKE', 'organizations.name', $filter['name']]);
        }

        $shelters = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->asArray();

        return new CommonList(
            'shelters',
            $shelters->all(),
            $shelters->count(),
            $page,
            $limit
        );
    }

    public static function getAllVaccines()
    {
        $rows = (new \yii\db\Query())
            ->select(['tmc.tmc.id', 'tmc.tmc.name', 'tmc.tmc.produced'])
            ->from('tmc.tmc')
            ->where(['type' => 'vaccine'])
            ->andWhere(['is_deleted' => false])
            ->orderBy(['tmc.name' => SORT_ASC])
            ->all();


        return $rows;
    }

    public static function getAllForAct(array $filter = []): array
    {
        $filter = self::validateFilter($filter);

        $query = Pets::find()
            ->with(self::getIncludes($filter))
            ->where(['id_pet_tmp' => null])
        ;

        if (!empty($filter)) {
            $query = self::applyFilter($query, $filter, true);
        }

        $tmcs = [];
        $petOwners = [];

        $pets = $query->asArray()->all();
        foreach ($pets as $pet) {
            $pet = self::setFullNameToSpecialist($pet);


            foreach ($pet['visitShelter'] as $visitShelter) {
                if (isset($visitShelter['visitServiceTmcs'][0]['balance']['id'])) {
                    $visitServiceTmcs = $visitShelter['visitServiceTmcs'][0];
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

                    $specialists[$visitShelter['author']] = $visitShelter['author_ref'];
                    $petOwners[$key] = array_unique(array_merge($petOwners[$key] ?? [], self::findPetOwners($pet['visitShelter'])));


                    $tmcs[$key] = [
                        'petOwners' => $petOwners[$key],
                        'visitServiceTmcs' => $visitServiceTmcs,
                        'dosage' => $visitServiceTmcs['dosage'],
                        'measure' => $visitServiceTmcs['balance']['tmc']['measure'],
                        'balance' => $visitServiceTmcs['balance'],
                        'tmc' => $visitServiceTmcs['tmc'],
                        'count_sizes' => [
                            Pets::SIZE_SMALL => ($visitShelter['pets'][0]['size_id'] === Pets::SIZE_SMALL ? ++$countSmall : $countSmall),
                            Pets::SIZE_BIG => ($visitShelter['pets'][0]['size_id'] === Pets::SIZE_BIG ? ++$countBig : $countBig),
                        ],
                        'species' => [
                            '9' => ($visitShelter['pets'][0]['id_species'] === 9 ? ++$cats : $cats),
                            '25' => [
                                Pets::SIZE_SMALL => ($visitShelter['pets'][0]['id_species'] === 25 && $visitShelter['pets'][0]['size_id'] === Pets::SIZE_SMALL ? ++$dogsSmall : $dogsSmall),
                                Pets::SIZE_BIG => ($visitShelter['pets'][0]['id_species'] === 25 && $visitShelter['pets'][0]['size_id'] === Pets::SIZE_BIG ? ++$dogsBig : $dogsBig),
                            ],
                        ],
                        'specialists' => $specialists,

                        'total_count' =>  number_format($tmcUsed,2),
                        'total_count_utilize' => number_format($tmcUtil,2),
                    ];
                }
            }

        }

        return $tmcs;
    }

    /**
     * Ищет и возвращает список владельцев
     *
     * @param array $visitShelters
     * @return array
     */
    private static function findPetOwners(array $visitShelters): array
    {
        $owners = [];
        $noOwners = [];

        foreach ($visitShelters as $visitShelter){
            foreach ($visitShelter['pets'][0]['pets_to_owner'] as $petsToOwner){
                if($petsToOwner['owner_type']['is_owner']){
                    $owners[] =  $petsToOwner['id_owner'];
                } else {
                    $noOwners[] =  $petsToOwner['id_owner'];
                }
            }
        }

        return $owners ? $owners : $noOwners;
    }

    /**
     * @param $disease_id
     * @param $date
     * @return Query
     */
    private static function buildSubQueryHasVaccineOutsideDate($disease_id, $date): Query
    {
        return (new Query())
            ->select(new Expression(' true '))
            ->from('pet_rabies_vaccination')
            ->leftJoin('tmc.tmc as tmc', 'pet_rabies_vaccination.id_vaccine = tmc.id')
            ->leftJoin('tmc.tmc_to_diseases as ttd', 'ttd.id_tmc = tmc.id')
            ->where([
                'AND',
                ['ttd.id_disease' => $disease_id],
                new Expression("pet_rabies_vaccination.valid_until > '$date'"),
                new Expression('pets.id = pet_rabies_vaccination.id_pet')
            ])
            ->limit(1);
    }

    /**
     * Дёрнем fullname из user и уберём лишние данные user из ответа
     *
     * @param array $pet
     * @return array
     */
    private static function setFullNameToSpecialist(array $pet)
    {
        foreach($pet['visitShelter'] as &$visitShelter) {
            if (isset($visitShelter['author_ref']) && isset($visitShelter['author_ref']['user'])) {
                $visitShelter['author_ref']['fullname'] = $visitShelter['author_ref']['user']['fullname'];
                unset($visitShelter['author_ref']['user']);
            }
        }

        return $pet;
    }
}
