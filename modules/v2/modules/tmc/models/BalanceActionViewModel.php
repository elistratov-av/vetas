<?php

namespace app\modules\v2\modules\tmc\models;

use app\common\components\rbac\Role;
use app\models\db\Organizations;
use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\TmcBase;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Отображение данных действий над балансом
 * Class BalanceActionViewModel
 *
 * @package app\modules\v2\modules\tmc\models
 * @author Aleksandr Roik
 */
class BalanceActionViewModel
{
    /**
     * Возвращает список сущностей
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return CommonList
     * @throws InvalidConfigException
     */
    public function getList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->validateFilter($page, $limit, $filter);

        $query = BalanceAction::find();
        $this
            ->addSelect($query)
            ->applyFilter($query, $filter)
            ->applyFilterByOrg($query, $filter)
            ->applyFilterBySpecialist($query, $filter)
            ->orderBy($query);

        $count = clone $query;
        $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);

        $result = BalanceActionAccessRules::addAccessFlagsToList($query->all());

        return new CommonList('action', $result, $count->count(), $page, $limit);
    }



    /**
     * Фильтрация по организации
     *
     * @param ActiveQuery $query
     * @param $filter
     * @return $this
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    protected function applyFilterByOrg($query, $filter)
    {
        $id_organization = empty($filter['id_organization']) ? null : $filter['id_organization'] ;

        if (empty($id_organization)) {
            /*
             * Техник из Мосвета видит все
             */
            $user_org = $this->getSpecialistOrgId();
            if (\Yii::$app->user->can(Role::ROLE_TECHNIC_MTO) && $user_org == Organizations::MOS_VET_UNION_ID) {
                return $this;
            }

            $query->andWhere([
                'OR',
                ['from_id_organization' => $this->getSpecialistOrgId()],
                ['to_id_organization' => $this->getSpecialistOrgId()],
            ]);
        } else {
            $query->andWhere([
                'OR',
                [
                    'AND',
                    ['from_id_organization' => $this->getSpecialistOrgId()],
                    ['to_id_organization' => $id_organization],
                ],
                [
                    'AND',
                    ['from_id_organization' => $id_organization],
                    ['to_id_organization' => $this->getSpecialistOrgId()],
                ]
            ]);
        }

        return $this;
    }

    /**
     * Фильтрация по спецу
     *
     * @param $query
     * @param $filter
     * @return $this
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function applyFilterBySpecialist($query, $filter)
    {
        $id_specialist = empty($filter['id_specialist']) ? null : $filter['id_specialist'];

        // Техник пусть выбирает что хочет - все равно он ограничен по фильтру организаций
        // остальные - видят только то, что относятся к ним
        if (\Yii::$app->user->can(Role::ROLE_TECHNIC_MTO) && !empty($id_specialist)) {
            $query
                ->andWhere([
                    'OR',
                    ['IN', 'from_id_specialist', $id_specialist],
                    ['IN', 'to_id_specialist', $id_specialist],
                ]);
        } elseif (\Yii::$app->user->can(Role::ROLE_TECHNIC_MTO) == false && !empty($id_specialist)) {
            $query
                ->andWhere([
                    'OR',
                    [
                        'AND',
                        ['IN', 'from_id_specialist', $this->getSpecialistId()],
                        ['IN', 'to_id_specialist', $id_specialist],
                    ],
                    [
                        'AND',
                        ['IN', 'from_id_specialist', $id_specialist],
                        ['IN', 'to_id_specialist', $this->getSpecialistId()],
                    ],
            ]);
        } elseif (\Yii::$app->user->can(Role::ROLE_TECHNIC_MTO) == false) {
            $query
                ->andWhere([
                    'OR',
                    ['IN', 'from_id_specialist', $this->getSpecialistId()],
                    ['IN', 'to_id_specialist', $this->getSpecialistId()],
                ]);
        }

        return $this;
    }

    /**
     * Составление SELECT
     *
     * @param ActiveQuery $query
     * @return $this
     */
    protected function addSelect(ActiveQuery $query)
    {
        $tableName = BalanceAction::tableName();

        $query
            ->select([
                $tableName . '.id',
                $tableName . '.initiator_date',
                $tableName . '.action',
                $tableName . '.status',
                $tableName . '.initiator_id_specialist',
                $tableName . '.initiator_comment',
                $tableName . '.acceptor_date',
                $tableName . '.acceptor_id_specialist',
                $tableName . '.acceptor_comment',
                $tableName . '.from_id_organization',
                $tableName . '.from_id_specialist',
                $tableName . '.to_id_organization',
                $tableName . '.to_id_specialist',
                $tableName . '.created_at',
            ])
            ->asArray()
            ->with([
                    'actionTmcList'       => function ($query) {
                        /* @var ActiveQuery $query */
                        $query
                            ->select([
                                'id',
                                'id_action',
                                'id_balance_tmc',
                                'id_dosage',
                                'count',
                                'count_selected',
                                'count_production_form',
                                'comment',
                                'write_off_all',
                            ])
                            ->with([
                                'balance' => function ($query) {
                                    $query
                                        ->select([
                                            'id',
                                            'id_tmc',
                                            'id_production_form',
                                            'type_tmc',
                                            'count',
                                            'price',
                                            'inventory_number',
                                            'expiration_date',
                                        ])
                                        ->with([
                                            'tmc'             => function ($query) {
                                                $query
                                                    ->select([
                                                        'id',
                                                        'name',
                                                        'type',
                                                        'id_measure'
                                                    ])
                                                    ->with([
                                                            'measure' => function ($query) {
                                                                $query
                                                                    ->select([
                                                                        'id',
                                                                        'name',
                                                                    ]);
                                                            }
                                                        ]
                                                    );
                                            },
                                            'production_form' => function ($query) {
                                                $query
                                                    ->select([
                                                        'id',
                                                        'id_tmc',
                                                        'type_tmc',
                                                        'name',
                                                        'volume',
                                                    ]);
                                            },
                                        ]);

                                },
                                'dosage'  => function ($query) {
                                    $query
                                        ->select([
                                            'id',
                                            'name',
                                            'id_measure',
                                            'id_species',
                                            'dosage',
                                        ])
                                        ->with([
                                                'measure' => function ($query) {
                                                    $query
                                                        ->select([
                                                            'id',
                                                            'name',
                                                        ]);
                                                },
                                                'species' => function ($query) {
                                                    $query
                                                        ->select([
                                                            'id',
                                                            'name',
                                                        ]);
                                                }
                                            ]
                                        );
                                },
                            ]);
                    },
                    'initiatorSpecialist' => function ($query) {
                        /* @var ActiveQuery $query */
                        $query
                            ->select([
                                'id',
                                'id_user',
                            ])
                            ->with([
                                'user' => function ($query) {
                                    $query
                                        ->select([
                                            'id',
                                            'f_fio',
                                            'i_fio',
                                            'o_fio',
                                        ]);
                                }
                            ]);
                    },
                    'acceptorSpecialist'  => function ($query) {
                        /* @var ActiveQuery $query */
                        $query
                            ->select([
                                'id',
                                'id_user',
                            ])
                            ->with([
                                'user' => function ($query) {
                                    $query
                                        ->select([
                                            'id',
                                            'f_fio',
                                            'i_fio',
                                            'o_fio',
                                        ]);
                                }
                            ]);
                    },
                    'fromSpecialist'      => function ($query) {
                        /* @var ActiveQuery $query */
                        $query
                            ->select([
                                'id',
                                'id_user',
                            ])
                            ->with([
                                'user' => function ($query) {
                                    $query
                                        ->select([
                                            'id',
                                            'f_fio',
                                            'i_fio',
                                            'o_fio',
                                        ]);
                                }
                            ]);
                    },
                    'toSpecialist'        => function ($query) {
                        /* @var ActiveQuery $query */
                        $query
                            ->select([
                                'id',
                                'id_user',
                            ])
                            ->with([
                                'user' => function ($query) {
                                    $query
                                        ->select([
                                            'id',
                                            'f_fio',
                                            'i_fio',
                                            'o_fio',
                                        ]);
                                }
                            ]);
                    },
                    'fromOrganization'    => function ($query) {
                        /* @var ActiveQuery $query */
                        $query
                            ->select([
                                'id',
                                'short_name',
                            ]);
                    },
                    'toOrganization'      => function ($query) {
                        /* @var ActiveQuery $query */
                        $query
                            ->select([
                                'id',
                                'short_name',
                            ]);
                    },
                ]
            );

        return $this;
    }

    /**
     * Фильтр
     *
     * @param ActiveQuery $query
     * @param array $filter
     * @return $this
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    protected function applyFilter(ActiveQuery $query, array $filter)
    {
        foreach ($filter as $attrName => $value) {
            switch ($attrName) {
                case 'action': //Действие (операция)
                    $query->andWhere([BalanceAction::tableName() . '.' . $attrName => $value]);
                    break;
                case 'status':
                    $query->andWhere(['IN', BalanceAction::tableName() . '.' . $attrName, $value]);
                    break;

                case 'id_tmc': //ТМЦ
                case 'type_tmc': //Категория ТМЦ
                    $subQuery = (new Query())
                        ->from(BalanceActionTmcList::tableName())
                        ->select(BalanceActionTmcList::tableName() . '.id')
                        ->where(BalanceAction::tableName() . '.id = id_action')
                        ->leftJoin(
                            Balance::tableName(),
                            Balance::tableName() . '.id = ' . BalanceActionTmcList::tableName() . '.id_balance_tmc'
                        )
                        ->andWhere([Balance::tableName() . '.' . $attrName => $value]);

                    $query->andWhere(['exists', $subQuery]);
                    break;

                case 'created_at_from': //Дата создания (начало)
                    $query->andWhere([
                        '>=',
                        BalanceAction::tableName() . '.' . 'created_at',
                        mb_strlen($value) === 10 ? date('Y-m-d 00:00:00', strtotime($value)) : $value
                    ]);
                    break;

                case 'created_at_to': //Дата создания (конец)
                    $query->andWhere([
                        '<=',
                        BalanceAction::tableName() . '.' . 'created_at',
                        mb_strlen($value) === 10 ? date('Y-m-d 23:59:59', strtotime($value)) : $value
                    ]);
                    break;
            }
        }

        return $this;
    }

    /**
     * Сортировка
     *
     * @param ActiveQuery $query
     * @return $this
     */
    protected function orderBy(ActiveQuery $query): self
    {
        $query
            ->orderBy([

                    new Expression("balance_action.status = '" . BalanceAction::STATUS_WAITING_CONFIRMATION . "' desc"),
                    new Expression("balance_action.status = '" . BalanceAction::STATUS_WAITING_EXECUTION . "' desc"),
                    new Expression("balance_action.status = '" . BalanceAction::STATUS_COMPLETED . "' desc"),
                    new Expression("balance_action.status = '" . BalanceAction::STATUS_REJECTED . "' desc"),
                    'balance_action.status'     => 'asc',
                    new Expression('balance_action.created_at desc'),

                ]
            );

        return $this;
    }

    /**
     * Валидация фильтра
     *
     * @param $page
     * @param $limit
     * @param array $filters
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function validateFilter(int $page, int $limit, array $filter)
    {
        if ($page < 1) {
            throw new BadRequestHttpException('Параметр page некорректный');
        }

        if ($limit < 1) {
            throw new BadRequestHttpException('Параметр limit некорректный');
        }

        if (empty($filter)) {
            return;
        }

        $filter = array_merge([
            'id_organization',
            'id_specialist',
            'id_tmc',
            'type_tmc',
            'created_at_from',
            'created_at_to',
            'action',
            'status',
        ], $filter);

        $rules = [
            [['id_organization', 'id_specialist', 'id_tmc'], 'integer'],
            [['created_at_from', 'created_at_to'], 'date'],
            [
                'action',
                'in',
                'range' => [
                    BalanceAction::ACTION_TRANSFER_TO_BALANCE,
                    BalanceAction::ACTION_TRANSFER_REQUEST,
                    BalanceAction::ACTION_RECYCLING,
                    BalanceAction::ACTION_WRITE_OFF,
                ]
            ],
            [
                'status',
                'each',
                'rule' => ['in', 'range' => [
                    BalanceAction::STATUS_COMPLETED,
                    BalanceAction::STATUS_REJECTED,
                    BalanceAction::STATUS_WAITING_EXECUTION,
                    BalanceAction::STATUS_WAITING_CONFIRMATION,
                ]],
            ],
            [
                'type_tmc',
                'in',
                'range' => [
                    TmcBase::TYPE_VACCINE,
                    TmcBase::TYPE_EQUIPMENT,
                    TmcBase::TYPE_EXP_MATERIAL,
                    TmcBase::TYPE_DRUG
                ]
            ],
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

    }

    /**
     * @throws \Throwable
     * @throws ForbiddenHttpException
     */
    protected function getSpecialistId()
    {
        /* @var \app\common\models\UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        $id = $user->specialist->id;
        if ($id == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }

        return $id;
    }

    /**
     * @throws \Throwable
     * @throws ForbiddenHttpException
     */
    protected function getSpecialistOrgId()
    {
        /* @var \app\common\models\UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        $id_organization = $user->specialist->id_organization;
        if ($id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }

        return $id_organization;
    }
}
