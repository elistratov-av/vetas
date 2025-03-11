<?php

namespace app\modules\v2\modules\visit\models;

use app\common\models\UserModel;
use app\models\db\tmc\Category;
use app\models\db\tmc\TmcBase;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitServiceTmcArchive;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class ServiceTmcsModel
{

    use VisitTrait, VisitServiceTmcTrait;

    /**
     * @var \app\models\db\Visits
     */
    public $visit;

    /**
     * @var UserModel|null
     */
    protected $_user;

    /**
     * Получение доступных или привязанных к услуге приема балансовых ТМЦ
     *
     * @param int $id_visit
     * @param int $id_visits_gov_service
     * @return array
     * @throws BadRequestHttpException
     */
    public function getBalanceTMC(int $id_visit, int $id_visits_gov_service)
    {
        /*
         * Валидируем $id_visit и $id_visits_gov_service
         */
        $this->validateVisit_AND_VisitsGovService(
            $id_visit,
            $id_visits_gov_service,
            false
        );

        return [
            'available_balance_tmc' => $this->getAvailableBalanceTmc($id_visit, $id_visits_gov_service),
            'selected_balance_tmc'  => $this->getSelectedBalanceTmc($id_visit, $id_visits_gov_service),
            'selected_other_tmc'    => $this->getSelectedTmc($id_visit, $id_visits_gov_service),
            'archive_tmc'           => $this->getAchiveTmc($id_visit, $id_visits_gov_service),
        ];

    }

    /**
     * Список выбранных ВНЕбалансовых ТМЦ
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function getSelectedTmc($id_visit, $id_visits_gov_service)
    {
        $tmc_fields = $this->getTmcQueryFields();

        return VisitServiceTmc::find()
            ->select([
                'visit_service_tmc.id',
                'visit_service_tmc.id_visit',
                'visit_service_tmc.id_visits_gov_service',
                'visit_service_tmc.id_tmc',
                'visit_service_tmc.type_tmc',
                'visit_service_tmc.id_balance_tmc',
                'visit_service_tmc.id_dosage',
                'visit_service_tmc.count_selected',
                'visit_service_tmc.price',
                'visit_service_tmc.write_off_pack_form',
                'visit_service_tmc.apply_discount',
                'visit_service_tmc.apply_night_discount',
                'visit_service_vaccination.batch',
                'visit_service_vaccination.expiry_date',
                'visit_service_vaccination.production_date',
                'visit_service_vaccination.valid_until',
            ])
            ->joinWith(
                [
                    'visitServiceVaccination',
                ])
            ->with([
                'tmc'                => function ($query) use ($tmc_fields) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query
                        ->select($tmc_fields)
                        ->with([
                            'measure' => function ($query) {
                                /* @var $query \yii\db\ActiveQuery */
                                $query
                                    ->select([
                                        'id',
                                        'name',
                                    ]);
                            },
                            'dosages' => function ($query) {
                                /* @var $query \yii\db\ActiveQuery */
                                $query
                                    ->select([
                                        'tmc.dosages.id',
                                        'tmc.dosages.type_tmc',
                                        'tmc.dosages.id_tmc',
                                        'tmc.dosages.dosage',
                                        'tmc.dosages.name',
                                        //'tmc.dosages.id_measure',
                                        //'tmc.dosages.id_species',
                                        //'tmc.dosages.id_disease',
                                        //'tmc.dosages.created_by',
                                        //'tmc.dosages.updated_by',
                                        //'tmc.dosages.created_at',
                                        //'tmc.dosages.updated_at',
                                        //'tmc.dosages.age_range',
                                        //'tmc.dosages.weight_range',
                                    ]);
                            },
                        ]);
                },
                'visitServiceTmcPet' => function ($query) {
                    $query->select([
                        'id',
                        'id_pet',
                        'id_visit_service_tmc',
                    ]);
                },
            ])
            ->where([
                'AND',
                ['id_visit' => $id_visit],
                ['id_visits_gov_service' => $id_visits_gov_service],
                ['IS', 'id_balance_tmc', null],
            ])
            ->asArray()
            ->all();
    }

    /**
     * Список выбранных балансовых ТМЦ
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function getSelectedBalanceTmc($id_visit, $id_visits_gov_service)
    {
        $tmc_fields = $this->getTmcQueryFields();

        return VisitServiceTmc::find()
            ->select([
                'visit_service_tmc.id',
                'visit_service_tmc.id_visit',
                'visit_service_tmc.id_visits_gov_service',
                'visit_service_tmc.id_tmc',
                'visit_service_tmc.type_tmc',
                'visit_service_tmc.id_balance_tmc',
                'visit_service_tmc.id_dosage',
                'visit_service_tmc.count_selected',
                'visit_service_tmc.count_production_form',
                'visit_service_tmc.price',
                'visit_service_tmc.write_off_pack_form',
                'visit_service_tmc.apply_discount',
                'visit_service_tmc.apply_night_discount',
                'tmc.balance.id_organization',
                'tmc.balance.id_specialist',
                'visit_service_vaccination.batch',
                'visit_service_vaccination.expiry_date',
                'visit_service_vaccination.production_date',
                'visit_service_vaccination.valid_until',
            ])->leftJoin(
                'tmc.balance',
                'tmc.balance.id = visit_service_tmc.id_balance_tmc'
            )->joinWith(
                [
                    'visitServiceVaccination',
                ])
            ->where([
                'AND',
                ['visit_service_tmc.id_visit' => $id_visit],
                ['visit_service_tmc.id_visits_gov_service' => $id_visits_gov_service],
                ['IS NOT', 'id_balance_tmc', null],
            ])
            ->with([
                'balance'            => function ($query) use ($tmc_fields) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query->select([
                        'tmc.balance.id',
                        'tmc.balance.id_tmc',
                        'tmc.balance.type_tmc',
                        'tmc.balance.expiration_date',
                        'tmc.balance.registration_date',
                        'tmc.balance.inventory_number',
                        'tmc.balance.price',
                    ])
                        ->with([
                            'categories' => function ($query) {
                                /** @var \yii\db\ActiveQuery $query */
                                $query->select([
                                    'id',
                                    'name',
                                    'description'
                                ]);
                            },
                            'tmc'        => function ($query) use ($tmc_fields) {
                                /* @var $query \yii\db\ActiveQuery */
                                $query
                                    ->select($tmc_fields)
                                    /*
                                     *  Только балансовые
                                     */
                                    ->innerJoin('tmc.balance',
                                        'tmc.balance.id_tmc = tmc.tmc.id AND tmc.balance.type_tmc = tmc.tmc.type'
                                    );
                            }
                        ]);
                },
                'visitServiceTmcPet' => function ($query) {
                    $query->select([
                        'id',
                        'id_pet',
                        'id_visit_service_tmc',
                    ]);
                },
            ])
            ->asArray()
            ->all();
    }

    /**
     * Список доступных для выбора ТМЦ
     * Нам надо показать доступные пользователю ТМЦ по:
     * 1. Категориям
     * И
     * 2. Все доступные ТМЦ в категории "Другие"
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function getAvailableBalanceTmc($id_visit, $id_visits_gov_service)
    {
        /*
         * -------
         * ПРИ ИСПРАВЛЕНИИ ПРИВЕДИ В СООТВЕТСТВИЕ
         * ServiceTmcsModelSave::loadBalanceTmsInfo() под новые требования
         * чтобы валидация их учитывала
         * --------
         */
        $methods = [
            'findAvailableBalanceTmcByCategory',
            'findAvailableBalanceTmcByOtherCategory',
        ];

        $result = [];
        foreach ($methods as $method) {
            $tmcs = $this->{$method}($id_visit, $id_visits_gov_service);
            if ($tmcs) {
                $result = array_merge($result, $tmcs);
            }
        }

        return $result;
    }

    /**
     * Ищем ТМЦ, доступные у специалиста на балансе в разрезе категорий
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @return array|\yii\db\ActiveRecord[]
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function findAvailableBalanceTmcByCategory($id_visit, $id_visits_gov_service)
    {
        $visit = Visits::findOne($id_visit);
        $id_specialist = $visit->visitsSpecialist ? $visit->visitsSpecialist->id_specialist : null;
        $id_organization = $visit->id_organization;

        $balance_filter = [
            'AND',
            ['!=', 'tmc.balance.type_tmc', 'equipment'], // оборудование в этом методе не нужно
            [
                'or',
                ['>', 'tmc.balance.count', 0],
                [
                    'exists',
                    (new Query())
                        ->select('id')
                        ->from('visit_service_tmc')
                        ->andWhere([
                            'id_tmc'                => new Expression("tmc.balance.id_tmc"),
                            'id_visit'              => $id_visit,
                            'id_visits_gov_service' => $id_visits_gov_service,
                        ])
                ],

            ],
            new Expression('tmc.balance.expiration_date >= NOW()::date'),
            [
                'OR',
                [
                    // Расходники могут и со своего баланса и с баланса организации
                    'AND',
                    ['tmc.balance.type_tmc' => 'exp_material'],
                    ['tmc.balance.id_organization' => $id_organization],
                    ['IS', 'tmc.balance.id_specialist', null],
                ],
                [
                    // На балансе спеца
                    'AND',
                    ['tmc.balance.id_organization' => $id_organization],
                    ['tmc.balance.id_specialist' => $id_specialist]
                ],
            ]
        ];

        $tmc_fields = $this->getTmcQueryFields();
        $tmc_with = $this->getTmcQueryWith($balance_filter);

        return Category::find()
            ->select([
                'tmc.category.id',
                'tmc.category.name',
                'tmc.category.description',
            ])
            ->leftJoin('tmc.category_to_gov_services',
                'tmc.category_to_gov_services.id_category = tmc.category.id'
            )
            ->with([
                'tmcs' => function ($query) use ($balance_filter, $tmc_fields, $tmc_with) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query->select($tmc_fields)
                        /*
                         *  Только балансовые
                         */
                        ->innerJoin('tmc.balance',
                            'tmc.balance.id_tmc = tmc.tmc.id AND tmc.balance.type_tmc = tmc.tmc.type'
                        )
                        ->with($tmc_with)
                        ->where($balance_filter);
                }
            ])
            ->where(['tmc.category_to_gov_services.id_service' => $this->visits_gov_service->id_service])
            ->asArray()
            ->all();
    }

    /**
     * Ищем все ТМЦ, доступные у специалиста на балансе для категории "Другие"
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @return array
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function findAvailableBalanceTmcByOtherCategory($id_visit, $id_visits_gov_service)
    {
        $visit = Visits::findOne($id_visit);
        $id_specialist = $visit->visitsSpecialist ? $visit->visitsSpecialist->id_specialist : null;
        $id_organization = $visit->id_organization;

        $balance_filter = [
            'AND',
            ['!=', 'tmc.balance.type_tmc', 'equipment'], // оборудование в этом методе не нужно
            [
                'or',
                ['>', 'tmc.balance.count', 0],
                [
                    'exists',
                    (new Query())
                        ->select('id')
                        ->from('visit_service_tmc')
                        ->andWhere([
                            'id_tmc'                => new Expression("tmc.balance.id_tmc"),
                            'id_visit'              => $id_visit,
                            'id_visits_gov_service' => $id_visits_gov_service,
                        ])
                ],

            ],
            new Expression('tmc.balance.expiration_date >= NOW()::date'),
            [
                'OR',
                [
                    // Расходники могут и со своего баланса и с баланса организации
                    'AND',
                    ['tmc.balance.type_tmc' => 'exp_material'],
                    ['tmc.balance.id_organization' => $id_organization],
                    ['IS', 'tmc.balance.id_specialist', null],
                ],
                [
                    // На балансе спеца
                    'AND',
                    ['tmc.balance.id_organization' => $id_organization],
                    ['tmc.balance.id_specialist' => $id_specialist]
                ],
            ]
        ];

        $tmc_fields = $this->getTmcQueryFields();
        $tmc_with = $this->getTmcQueryWith($balance_filter);
        $tmcs = TmcBase::find()
            ->select($tmc_fields)
            /*
             *  Только балансовые
             */
            ->innerJoin('tmc.balance',
                'tmc.balance.id_tmc = tmc.tmc.id AND tmc.balance.type_tmc = tmc.tmc.type'
            )
            ->with($tmc_with)
            ->where($balance_filter)
            ->asArray()
            ->all();

        return $tmcs ? [
            [
                'id'          => -10000,
                'name'        => 'Другие',
                'description' => 'Ранее использованные ТМЦ, оставшиеся без категории',
                'tmcs'        => $tmcs,
            ]
        ] : [];
    }

    /**
     * Список архивных ТМЦ (для старых визитов)
     *
     * @param int $id_visit
     * @param int $id_visits_gov_service
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function getAchiveTmc(int $id_visit, int $id_visits_gov_service)
    {
        return VisitServiceTmcArchive::find()
            ->where([
                'id_visit'              => $id_visit,
                'id_visits_gov_service' => $id_visits_gov_service,
            ])
            ->asArray()
            ->all();
    }

    /**
     * Список полей для выборки из tmc.tmc
     *
     * @return string[]
     */
    protected function getTmcQueryFields()
    {
        return [
            'tmc.tmc.id',
            'tmc.tmc.type',
            'tmc.tmc.name',
            //'tmc.tmc.basis',
            //'tmc.tmc.dealer',
            //'tmc.tmc.description',
            //'tmc.tmc.excipients',
            //'tmc.tmc.form_description',
            'tmc.tmc.id_measure',
            'tmc.tmc.unit',
            //'tmc.tmc.packaging',
            'tmc.tmc.produced',
            //'tmc.tmc.registered',
            'tmc.tmc.is_deleted',
            //'tmc.tmc.created_at',
            //'tmc.tmc.created_by',
            //'tmc.tmc.updated_at',
            //'tmc.tmc.updated_by',
            'tmc.tmc.is_uncountable'
        ];
    }

    /**
     * Выборка связанных объектов с балансом
     *
     * @param $balance_filter
     * @return array
     */
    protected function getTmcQueryWith($balance_filter)
    {
        return [
            'measure'  => function ($query) {
                /* @var $query \yii\db\ActiveQuery */
                $query
                    ->select([
                        'id',
                        'name',
                    ]);
            },
            'dosages'  => function ($query) {
                /* @var $query \yii\db\ActiveQuery */
                $query
                    ->select([
                        'tmc.dosages.id',
                        'tmc.dosages.type_tmc',
                        'tmc.dosages.id_tmc',
                        'tmc.dosages.dosage',
                        'tmc.dosages.name',
                        //'tmc.dosages.id_measure',
                        //'tmc.dosages.id_species',
                        //'tmc.dosages.id_disease',
                        //'tmc.dosages.created_by',
                        //'tmc.dosages.updated_by',
                        //'tmc.dosages.created_at',
                        //'tmc.dosages.updated_at',
                        //'tmc.dosages.age_range',
                        //'tmc.dosages.weight_range',
                    ]);
            },
            'balances' => function ($query) use ($balance_filter) {
                /* @var $query \yii\db\ActiveQuery */
                $query
                    ->select([
                        'tmc.balance.id',
                        'tmc.balance.id AS id_balance_tmc', // для удобства
                        'tmc.balance.id_tmc',
                        'tmc.balance.type_tmc',
                        'tmc.balance.id_organization',
                        'tmc.balance.id_specialist',
                        'tmc.balance.id_production_form',
                        'tmc.balance.count',
                        'tmc.balance.expiration_date',
                        'tmc.balance.registration_date',
                        'tmc.balance.inventory_number',
                        'tmc.balance.price',
                        'tmc.balance.equipment_condition',
                        'tmc.balance.manufactured_number',
                        //'tmc.balance.old_id',
                        //'tmc.balance.created_at',
                        //'tmc.balance.created_by',
                        //'tmc.balance.updated_at',
                        //'tmc.balance.updated_by',
                        'tmc.balance.count_in_production_form'
                    ])
                    ->with([
                        'production_form' => function ($query) {
                            /* @var $query \yii\db\ActiveQuery */
                            $query
                                ->select([
                                    'tmc.production_form.id',
                                    'tmc.production_form.id_tmc',
                                    'tmc.production_form.type_tmc',
                                    'tmc.production_form.name',
                                    'tmc.production_form.volume',
                                    'tmc.production_form.is_utilize',
                                    'tmc.production_form.is_deleted',
                                ]);
                        }
                    ])
                    ->where($balance_filter);
            },
        ];
    }
}
