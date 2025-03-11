<?php

namespace app\modules\v2\modules\pets\models;

use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\models\db\Pets;
use app\models\db\Specialists;
use app\models\db\Users;
use app\models\db\VisitPets;
use app\models\db\Visits;
use app\modules\v2\modules\pets\skeletons\services\Lists;
use yii\base\DynamicModel;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class PetServicesModel
{
    public $id_organization;

    /**
     * DiscountModel constructor
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function __construct()
    {
        $user = \Yii::$app->user->getIdentity();
        $this->id_organization = $user->specialist->id_organization;
        if ($this->id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }
    }

    /**
     * Услуги оказанные животному
     *
     * @param int $page
     * @param int $limit
     * @param int $id_pet
     * @param array $filter
     * @return Lists
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     *
     * TODO:multiple-pets-services Учитывать услуги для неск. животных
     */
    public function list($page, $limit, $id_pet, $filter)
    {
        $pet = Pets::findOne(['id' => $id_pet]);

        if ($pet->is_main === true && !empty($pet->duplicates)) {
            // На вкладке "Оказанные услуги"  основного животного отображается сводная информация по приемам,
            // включая приемы животных-дублей
            $duplicates_ids = ArrayHelper::getColumn($pet->duplicates, 'id');
            array_unshift($duplicates_ids, $id_pet);
            $id_pet = $duplicates_ids;
        }

        $this->validateFilter($filter);

        $query = Visits::find()
            ->select([
                'visits.*',
                'public.visits_gov_services.id_pet AS pet_id_realy',
                'public.pets.name AS pet_name',
                'public.gov_services.id_service_type AS service_type',
                'lower(time_range)::date',
            ])
            ->joinWith([
                'visitsGovServices' => function (ActiveQuery $query) use ($id_pet, $filter) {
                    $query
                        ->leftJoin('pets p', 'p.id = visits_gov_services.id_pet')
                    ;
                    return $query->select('visits_gov_services.id, id_visit, id_service, visits_gov_services.id_pet, p.name pet_name');
                }
            ])
            ->joinWith([
                'visitsGovServices.service' => function ($query) use ($filter) {
                    /** @var ActiveQuery $query */
                    $fullTrimValidator = new FullTrimValidator();

                    if (!empty($filter['cod'])) {
                        if (!is_array($filter['cod'])) {
                            $filter['cod'] = [$filter['cod']];
                        }
                        $codes = [];
                        foreach ($filter['cod'] as $code) {
                            $codes[] = $fullTrimValidator->validateValue($code);
                        }

                        $query->andFilterWhere($this->prepareCodesCondition($codes));
                    }

                    if (!empty($filter['service_name'])) {
                        $query->andWhere(['ILIKE', 'gov_services.name', $filter['service_name']]);
                    }



                    if (!empty($filter['id_service_type'])) {
                        $query->andWhere(['gov_services.id_service_type' => $filter['id_service_type']]);
                    }
                    return $query;
                }
            ])
            ->joinWith('visitsGovServices.service.serviceType')
            ->with('visitsGovServices.service.reports')
            ->with('visitsGovServices.service.outParams')
            ->joinWith([
                'organization' => function ($query) {
                    /** @var Query $query */
                    $query->select([
                        'organizations.id',
                        'organizations.name',
                        'organizations.short_name',
                    ]);
                }
            ])
            ->joinWith([
                'specialists' => function ($specialistQuery) {
                    /* @var $specialistQuery \yii\db\ActiveQuery */
                    $specialistQuery->joinWith('user', false)
                        ->select('specialists.id, users.fullname');
                }
            ])
            // ->andWhere(['public.visits.id_pet' => $id_pet])

            ->leftJoin("public.pets", "public.visits_gov_services.id_pet=public.pets.id")
            ->orderBy(['public.visits.id' => SORT_DESC]);

        //переработана логика выдачи услуг по запросу в связи с интеграцией с суперсервисом. Предыдущая логика не включала новые приемы, в статусе N. Переработанная логика включаеткак эти статусы, так и приемы, сохраненные по входящему Soap запросу от МПГУ. Старая логика в файле PetServiceModel_old   
        if (!empty($filter['is_refusal_to_vaccinate2'])) {
            if ($filter['is_refusal_to_vaccinate2'] === true) {
                $query
                    ->andWhere(['visits_gov_services.id_pet' => $id_pet])
                    ->andWhere(['!=', 'visits.channel', 5]);
            }
        } else {
            $query
                ->andWhere(['or', ['visits_gov_services.id_pet' => $id_pet]])
                ->orWhere(['and', ['public.visits.id_pet' => $id_pet]]);
        }

        $query->andWhere(['in', 'status', [VisitStatus::NEW , VisitStatus::IN_WORK, VisitStatus::FINISHED, VisitStatus::CANCELED, VisitStatus::TRANSFER,]]);


        // Применяем фильтры
        if (!empty($filter)) {
            $query = $this->applyFilter($filter, $query);
        }

        $records = $query
            ->asArray()
            ->all();

        // VETAIS-2159
        // костыль для заполнения reports для "виртуальных" отчетов (не имеющих печатной формы в reports)
        foreach ($records as &$record) {
            foreach ($record['visitsGovServices'] as &$visitsGovService) {
                if (empty($visitsGovService['service']) || !is_array($visitsGovService['service'])) {
                    continue;
                }
                if (empty($visitsGovService['service']['reports']) && !empty($visitsGovService['service']['outParams'])) {
                    $visitsGovService['service']['reports'] = [
                        [
                            'id' => -1,
                            'name' => 'VIRTUAL',

                        ],
                    ];
                }
            }
        }

        // Формируем ответ
        $result = new Lists(
            $records,
            $query
                ->limit(NULL)
                ->offset(null)
                ->count('distinct "visits"."id"')
        );

        $result->customPagination($page, $limit);

        //проверяем на наличие и добавляем ссылки guid на файлы из ЦХЭД


        foreach ($result->visits as &$item) {
            $rows = (new \yii\db\Query())
                ->select(['hash'])
                ->from('files')
                ->where([
                    'entity_id' => $item['id'],
                    'entity_type' => 'visits-mos-ru',
                ])
                ->all();

            if (count($rows) > 0) {
                $item["guid_file_superservice"] = $rows[0]['hash'];
            }
            ;

            if ($item['pet_name'] == null) {
                if ($item['id_pet'] != null) {
                    $pet_name_from_table = (new \yii\db\Query())
                        ->select(['name'])
                        ->from('pets')
                        ->where([
                            'id' => $item['id_pet']
                        ])
                        ->all();
                    $item['pet_name'] = $pet_name_from_table[0]['name'];
                } elseif ($item['pet_id_realy'] != null) {
                    $pet_name_from_table = (new \yii\db\Query())
                        ->select(['name'])
                        ->from('pets')
                        ->where([
                            'id' => $item['id_pet']
                        ])
                        ->all();
                    $item['pet_name'] = $pet_name_from_table[0]['name'];
                }
            }
        }
        return $result;
    }

    /**
     * @param array $codes
     * @return array
     */
    private function prepareCodesCondition($codes)
    {
        $codes = array_values(array_filter(array_unique($codes)));
        $condition = [];

        if (count($codes) == 1) {
            $condition = ['ilike', 'gov_services.cod', $codes[0]];
        } elseif (count($codes) > 1) {
            $condition[] = 'or';
            foreach ($codes as $code) {
                $condition[] = ['ilike', 'gov_services.cod', $code];
            }
        }

        return $condition;
    }
    /**
     * Валидирует фильтр для /v2/pets/services/list
     *
     * @param $filter
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateFilter($filter)
    {
        if (empty($filter)) {
            return;
        }

        $empty_filter = [
            "id_organization" => null,
            "date_from" => null,
            "date_to" => null,
            "id_specialists" => null,
            "cod" => null,
            "id_service_type" => null,
            "service_name" => null,
            "is_refusal_to_vaccinate2" => null,
        ];

        if (array_diff(array_keys($filter), array_keys($empty_filter))) {
            throw new BadRequestHttpException('Можно указывать только определенные поля в фильтре.');
        }

        $filter = array_merge($empty_filter, $filter);


        $rules = [
            [['id_organization', 'id_service_type'], 'integer'],
            [['service_name'], 'string'],
            [['date_from', 'date_to'], 'date'],
            [
                'cod',
                'string',
                'when' => function ($model) {
                    /* @var $model $this */
                    return !is_array($model->cod);
                },
            ],
            [
                'cod',
                'each',
                'rule' => ['string'],
                'when' => function ($model) {
                    /* @var $model $this */
                    return is_array($model->cod);
                },
            ]
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Применяет фильтры для /v2/pets/services/list
     *
     * @param array $filter
     * @param ActiveQuery $query
     * @return ActiveQuery
     * @throws \Exception
     */
    protected function applyFilter($filter, $query)
    {
        if (!empty($filter['id_organization'])) {
            $query->andWhere(['visits.id_organization' => $filter['id_organization']]);
        }

        if (!empty($filter['date_from'])) {
            $query->andWhere(['>=', 'visits.fact_end_dttm', $filter['date_from']]);
        }

        if (!empty($filter['date_to'])) {
            $date_to_str = $filter['date_to'];
            $date_to = \DateTime::createFromFormat('Y-m-d', $date_to_str);
            $date_to->modify('+1 day');
            $query->andWhere(['<=', 'visits.fact_end_dttm', $date_to->format('Y-m-d')]);
        }

        if (!empty($filter['id_specialists'])) {
            $query->andWhere(['specialists.id' => $filter['id_specialists']]);
        }

        return $query;
    }
}