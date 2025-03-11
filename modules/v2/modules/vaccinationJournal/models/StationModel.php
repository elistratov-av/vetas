<?php

namespace app\modules\v2\modules\vaccinationJournal\models;

use app\common\validators\FullTrimValidator;
use app\models\db\Pets;
use app\models\db\Specialists;
use app\models\db\Violation;
use app\models\db\Visits;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

/**
 * Class StationModel
 *
 * @package app\modules\v2\modules\vaccinationJournal\models
 */
class StationModel
{
    private static $include = [
        'services',
        'specialists',
        'author_ref',
        'author_ref.organization',
        'visitServiceTmcs',
        'visitServiceTmcs.balance',
        'visitServiceTmcs.balance.organization',
        'visitServiceTmcs.balance.tmc',
        'visitServiceTmcs.balance.production_form',
        'visitServiceTmcs.balance.tmc.measure',
        'visitServiceTmcs.tmc',
        'visitServiceTmcs.dosage',
        'visitServiceTmcs.dosage.measure',
        'pets',
        'pets.pet_identification',
        'pets.violations',
        'pets.breeds',
        'pets.species',
        'pets.pet_ectoparasites',
        'pets.pet_other_vaccinations',
        'pets.pet_rabies_vaccinations',
        'owner',
        'owner.fact_fias_addresses',
        'owner.contacts',
        'owner.phoneContacts',
        'owner.emailContacts',
        'visitServiceTmcPet',
        'anamnesis',
        'health',
    ];

    public static function get(int $id): ?array
    {
        return Visits::find()
            ->with(self::$include)
            ->where(['id' => $id, 'type' => Visits::TYPE_VISIT_VC])
            ->asArray()
            ->one();
    }

    /**
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     *
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public static function getAll(int $page = 1, int $limit = 10, array $filter = []): CommonList
    {
        $filter = self::validateFilter($filter);

        $query = Visits::find()
            ->distinct()
            ->with(self::$include)
            ->where(['type' => Visits::TYPE_VISIT_VC]);
   
        if (!empty($filter)) {
            $query = self::applyFilter($query, $filter);
        }

        $visits_count = (clone($query));

        $visits = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->orderBy(['created_at' => SORT_ASC])
            ->asArray();



        return new CommonList(
            'visits',
            $visits->all(),
            $visits_count->distinct()->count(),
            $page,
            $limit
        );
    }

    /**
     * @param array $filter
     *
     * @return array|null
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    private static function validateFilter(array $filter): ?array
    {
        $emptyFilter = [
            'vaccination_station_id' => null,
            'pet_owner' => null,
            'street' => null,
            'pet' => null,
            'pet_species_ids' => null,
            'specialist_id' => null,
            'is_refusal_to_vaccinate' => null,
            'name'=>null,
        ];
        $filter = array_merge($emptyFilter, $filter);

        $rules = [
            [['vaccination_station_id'], 'required'],
            [['vaccination_station_id', 'specialist_id'], 'integer'],
            [['pet_owner', 'street', 'pet', 'name'], 'string'],
            [['pet_owner', 'street', 'pet', 'name'], FullTrimValidator::class],
            ['is_refusal_to_vaccinate', 'boolean'],
            ['pet_species_ids', 'each', 'rule' => ['integer']],
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
     *
     * @return ActiveQuery
     */
    private static function applyFilter(ActiveQuery $query, array $filter): ActiveQuery
    {
        $query->joinWith(['pets', 'owner']);

        if (!empty($filter['vaccination_station_id'])) {
            $query->andWhere(['visits.vaccination_station_id' => $filter['vaccination_station_id']]);
        } else {
            $query->andWhere('1 != 1');
        }

        if (!empty($filter['pet_owner'])) {
            $pet_owners_fullname = $filter['pet_owner'];
            $query->andWhere([
                'or',
                ['pet_owners.id' => intval($filter['pet_owner'])],
                // ['pet_owners.fullname' => $filter['pet_owner']]
                ['like', 'pet_owners.fullname', "%$pet_owners_fullname%", false]
            ]);
        }

        if (!empty($filter['street'])) {
            $query->leftJoin('fias_addresses ffa', 'pet_owners.id_fact_fias_address = ffa.id');
            $query->andWhere(['ILIKE', 'ffa.full_address', $filter['street']]);
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
            $query->andWhere(['visits.author' => $filter['specialist_id']]);
        }

        if (!empty($filter['is_refusal_to_vaccinate'])) {
            $query->leftJoin('violation', 'violation.id_pet = pets.id');
            $query->andWhere(['AND',
                ['violation.id_type' => 4],
                ['IN', 'violation.state', Violation::ACTIVE_STATES]
            ]);
        }
        
        if (!empty($filter['name'])) {
            $query->leftJoin('public.contacts', 'public.pet_owners.id=public.contacts.entity_id');
            $query->andWhere(['public.contacts.name' =>$filter['name']]);
        }

        if (!empty($filter['pet_owner_phone'])) {
            $query->leftJoin('public.contacts', 'public.pet_owners.id=public.contacts.entity_id');
            $query->andWhere(['public.contacts.name' =>"+".$filter['pet_owner_phone']]);
        }

        return $query;
    }

    public static function getAllForAct(array $filter = []): array
    {
        $filter = self::validateFilter($filter);

        // Дёрнем user чтобы получить fullname, позже исключим user из ответа
        array_push(self::$include, 'author_ref.user');

        $query = Visits::find()
            ->with(self::$include)
            ->where(['type' => Visits::TYPE_VISIT_VC]);

        if (!empty($filter)) {
            $query = self::applyFilter($query, $filter);
        }

        $tmcs = [];
        $petOwners = [];

        /** @var Visits[] $visits */
        $visits = $query->orderBy(['start_dttm' => SORT_ASC])->asArray()->all();
        foreach ($visits as $visit) {
            if (isset($visit['visitServiceTmcs'][0]['balance']['id'])) {
                $visitServiceTmcs = $visit['visitServiceTmcs'][0];
                $key = $visitServiceTmcs['balance']['inventory_number'];
                if (isset($tmcs[$key])) {
                    $countSmall = $tmcs[$key]['count_sizes'][Pets::SIZE_SMALL];
                    $countBig = $tmcs[$key]['count_sizes'][Pets::SIZE_BIG];
                    $specialists = $tmcs[$key]['specialists'];
                    $other = $tmcs[$key]['species'][0];
                    $cats = $tmcs[$key]['species'][9];
                    $dogsSmall = $tmcs[$key]['species'][25][Pets::SIZE_SMALL];
                    $dogsBig = $tmcs[$key]['species'][25][Pets::SIZE_BIG];

                    $tmcUsed = $tmcs[$key]['total_count'] + $visitServiceTmcs['count'];
                    $tmcUtil = $tmcs[$key]['total_count_utilize'] + $visitServiceTmcs['count_utilize'];
                } else {
                    $countSmall = 0;
                    $countBig = 0;
                    $specialists = [];
                    $other = 0;
                    $cats = 0;
                    $dogsSmall = 0;
                    $dogsBig = 0;

                    $tmcUsed = $visitServiceTmcs['count'];
                    $tmcUtil = $visitServiceTmcs['count_utilize'];
                }

                $visit = self::setFullNameToSpecialist($visit);
                $specialists[$visit['author']] = $visit['author_ref'];


                if ($visit['owner']) {
                    $petOwners[$key][] = $visit['owner']['id'];
                }

                // Добавляем спецалиста - "владельца" ТМЦ
                if(
                    $visitServiceTmcs['balance'] &&
                    $visitServiceTmcs['balance']['id_specialist'] &&
                    !array_key_exists($visitServiceTmcs['balance']['id_specialist'], $specialists)
                ){
                    $specialists[$visitServiceTmcs['balance']['id_specialist']] = Specialists::find()
                        ->where(['id' => $visitServiceTmcs['balance']['id_specialist']])
                        ->with('organization')
                        ->one();

                }

                $tmcs[$key] = [
                    'petOwners' => $petOwners[$key] ?? [],
                    'visitServiceTmcs' => $visitServiceTmcs,
                    'dosage' => $visitServiceTmcs['dosage'],
                    'measure' => $visitServiceTmcs['balance']['tmc']['measure'],
                    'balance' => $visitServiceTmcs['balance'],
                    'tmc' => $visitServiceTmcs['tmc'],
                    'count_sizes' => [
                        Pets::SIZE_SMALL => ($visit['pets'][0]['size_id'] === Pets::SIZE_SMALL ? ++$countSmall : $countSmall),
                        Pets::SIZE_BIG => ($visit['pets'][0]['size_id'] === Pets::SIZE_BIG ? ++$countBig : $countBig),
                    ],
                    'species' => [
                        '0' => !in_array($visit['pets'][0]['id_species'], [9, 25]) ? ++$other : $other, //другие животные
                        '9' => ($visit['pets'][0]['id_species'] === 9 ? ++$cats : $cats),               //кот
                        '25' => [                                                                       //собака
                            Pets::SIZE_SMALL => ($visit['pets'][0]['id_species'] === 25 && $visit['pets'][0]['size_id'] === Pets::SIZE_SMALL ? ++$dogsSmall : $dogsSmall),
                            Pets::SIZE_BIG => ($visit['pets'][0]['id_species'] === 25 && $visit['pets'][0]['size_id'] === Pets::SIZE_BIG ? ++$dogsBig : $dogsBig),
                        ],
                    ],
                    'specialists' => $specialists,
                    'total_count' =>  number_format($tmcUsed,2),
                    'total_count_utilize' => number_format($tmcUtil,2),
                ];
            }
        }

        return $tmcs;
    }

    /**
     * Дёрнем fullname из user и уберём лишние данные user из ответа
     *
     * @param array $visit
     * @return array
     */
    private static function setFullNameToSpecialist(array $visit)
    {
        $visit['author_ref']['fullname'] = $visit['author_ref']['user']['fullname'];
        unset($visit['author_ref']['user']);
        return $visit;
    }
}
