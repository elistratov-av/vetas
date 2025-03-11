<?php

namespace app\modules\v2\modules\tmc\models;

use app\common\validators\FullTrimValidator;
use app\models\db\Diseases;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\models\db\tmc\Balance;
use app\models\db\tmc\CategoryToTmc;
use app\models\db\tmc\TmcBase;
use app\models\db\Users;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class BalanceViewModel
{

    protected $user_org_list = false;

    /**
     * @param $type_tmc
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    public function getList($type_tmc, int $page = 1, int $limit = 10, array $filter = [])
    {
        $filter = $this->validateFilter($type_tmc, $page, $limit, $filter);
        $query = $this->getQuery($type_tmc);

        if (!empty($filter)) {
            $this->applyFilter($type_tmc, $query, $filter);
        }

        $this->filterByOrg($query, $filter);
        $query->andWhere([
            'or',
            [
                'and',
                [
                    'tmc.balance.type_tmc' => [
                        TmcBase::TYPE_DRUG,
                        TmcBase::TYPE_EXP_MATERIAL,
                        TmcBase::TYPE_VACCINE,
                    ]
                ],
                ['>', 'count', 0],
            ],
            [
                'and',
                ['tmc.balance.type_tmc' => TmcBase::TYPE_EQUIPMENT],
                ['count' => null],
            ]
        ]);

        $count = clone $query;
        $query
            ->limit($limit)->orderBy('tmc.balance.registration_date DESC')->offset(($page - 1) * $limit);

        return new CommonList('balance', $query->all(), $count->count(), $page, $limit);
    }

    /**
     * Для комитета - все
     * Для мосветобъединения - все дочерние + оно само
     * Для остальных - мосветобъединение и все по линии ССБЖ
     *
     * @param ActiveQuery $query
     * @param $filter
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    protected function filterByOrg($query, $filter)
    {
        if (empty($filter['id_organization']) && empty($filter['allow_other_orgs'])) {
            $query->andWhere([
                'IN',
                'tmc.balance.id_organization',
                $this->getUserOrgList()
            ]);
        }
    }

    /**
     * @param $type_tmc
     * @param ActiveQuery $query
     * @param $filter
     */
    protected function applyFilter($type_tmc, $query, $filter)
    {
        // NAME
        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'tmc.tmc.name', $filter['name']]);
        }

        // CATEGORY_IDS
        if (!empty($filter['category_ids'])) {
            /*
             * У нас в balance уже есть type_tmc и id_tmc
             * Мы можем минуя связь с tmc.tmc заглянуть в таблицу tmc.category_to_tmc
             * Но так как категорий может быть много,
             * джоин приводит к дублированию балансовых записей,
             * потому - подзапрос
             */
            $sub_query = (new Query())
                ->from(CategoryToTmc::tableName())
                ->select('id_tmc')
                ->where([
                    'AND',
                    ['type_tmc' => $type_tmc],
                    ['IN', 'id_category', $filter['category_ids']]
                ]);

            $query->andWhere(['IN', 'tmc.balance.id_tmc', $sub_query]);
        }

        // ORGANIZATION NAME
        if (!empty($filter['organization_name']) && empty($filter['allow_other_orgs'])) {
            $query->innerJoin(
                Organizations::tableName(),
                'tmc.balance.id_organization = ' . Organizations::tableName() . '.id'
            );
            $query->andWhere([
                'OR',
                ['ILIKE', Organizations::tableName() . '.name', $filter['organization_name']],
                ['ILIKE', Organizations::tableName() . '.short_name', $filter['organization_name']],
            ]);
        }

        // ID ORGANIZATION
        if (!empty($filter['id_organization']) && empty($filter['allow_other_orgs'])) {
            $query->andWhere([
                'tmc.balance.id_organization' => $filter['id_organization']
            ]);
        }

        // ID SPECIALIST
        if (!empty($filter['id_specialist'])) {
            $query->andWhere([
                'tmc.balance.id_specialist' => $filter['id_specialist']
            ]);
        }

        // SPECIALIST FIO
        if (!empty($filter['specialist_fio'])) {
            $query
                ->innerJoin(
                    Specialists::tableName(),
                    'tmc.balance.id_specialist = ' . Specialists::tableName() . '.id'
                )
                ->innerJoin(
                    Users::tableName(),
                    Users::tableName() . '.id = ' . Specialists::tableName() . '.id_user'
                );
            $query->andWhere([
                'ILIKE',
                Users::tableName() . '.fullname',
                $filter['specialist_fio']
            ]);
        }

        // INVENTORY NUMBER
        if (!empty($filter['inventory_number'])) {
            $query->andWhere(['ILIKE', 'tmc.balance.inventory_number', $filter['inventory_number']]);
        }
        // MANUFACTURED NUMBER
        if (!empty($filter['manufactured_number'])) {
            $query->andWhere(['ILIKE', 'tmc.balance.manufactured_number', $filter['manufactured_number']]);
        }
        // EQUIPMENT CONDITION
        if (!empty($filter['equipment_condition'])) {
            $query->andWhere(['tmc.balance.equipment_condition' => $filter['equipment_condition']]);
        }

        // REGISTRATION DATE
        if (!empty($filter['registration_date_from'])) {
            $query->andWhere(['>=', 'tmc.balance.registration_date', $filter['registration_date_from']]);
        }

        if (!empty($filter['registration_date_to'])) {
            $query->andWhere(['<=', 'tmc.balance.registration_date', $filter['registration_date_to']]);
        }

        // EXPIRATION DATE
        if (!empty($filter['expiration_date_from'])) {
            $query->andWhere(['>=', 'tmc.balance.expiration_date', $filter['expiration_date_from']]);
        }

        if (!empty($filter['expiration_date_to'])) {
            $query->andWhere(['<=', 'tmc.balance.expiration_date', $filter['expiration_date_to']]);
        }

        if (!empty($filter['production_date_from'])) {
            $query->andWhere(['>=', 'tmc.balance.production_date', $filter['production_date_from']]);
        }

        if (!empty($filter['production_date_to'])) {
            $query->andWhere(['<=', 'tmc.balance.production_date', $filter['production_date_to']]);
        }

        if (!empty($filter['rabies_vaccines_only'])) {
            /** @var int $rabiesId */
            $rabiesId = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one()->id;
            $query->innerJoin(
                'tmc.tmc r_tmc',
                'tmc.balance.id_tmc = r_tmc.id 
                AND
                tmc.balance.type_tmc = r_tmc.type'
            );
            $query->innerJoin('tmc.tmc_to_diseases ttd', "ttd.id_tmc = r_tmc.id AND ttd.id_disease = $rabiesId");
        }
    }

    /**
     * @param $type_tmc
     * @param $page
     * @param $limit
     * @param $filter
     * @return array|void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function validateFilter($type_tmc, $page, $limit, $filter)
    {
        if (!is_numeric($page) || $page < 1) {
            throw new BadRequestHttpException('Параметр page некорректный');
        }

        if (!is_numeric($limit) || $limit < 0) {
            throw new BadRequestHttpException('Параметр limit некорректный');
        }
        if (empty($filter)) {
            return;
        }

        switch ($type_tmc) {
            case TmcBase::TYPE_DRUG:
            case TmcBase::TYPE_EXP_MATERIAL:
            case TmcBase::TYPE_VACCINE:
                $empty_filter = [
                    'name'                   => null,
                    'inventory_number'       => null,
                    'organization_name'      => null,
                    'category_ids'           => null,
                    'specialist_fio'         => null,
                    'registration_date_from' => null,
                    'registration_date_to'   => null,
                    'expiration_date_from'   => null,
                    'expiration_date_to'     => null,
                    'production_date_from'   => null,
                    'production_date_to'     => null,
                    'id_organization'        => null,
                    'allow_other_orgs'       => null,
                    'rabies_vaccines_only'   => null,
                    'id_specialist'          => null,
                ];

                $rules = [
                    [['id_organization', 'id_specialist'], 'integer'],
                    [['category_ids'], 'each', 'rule' => ['integer']],
                    [['inventory_number', 'organization_name', 'specialist_fio', 'name'], 'string'],
                    [['inventory_number', 'organization_name', 'specialist_fio', 'name'], FullTrimValidator::class],
                    [['registration_date_from', 'registration_date_to', 'expiration_date_from', 'expiration_date_to', 'production_date_from', 'production_date_to'], 'date'],
                    [['allow_other_orgs', 'rabies_vaccines_only'], 'boolean'],
                ];
                break;
            case TmcBase::TYPE_EQUIPMENT:
                $empty_filter = [ // для оборудования
                    'name'                   => null,
                    'inventory_number'       => null,
                    'organization_name'      => null,
                    'equipment_condition'    => null,
                    'manufactured_number'    => null,
                    'registration_date_from' => null,
                    'registration_date_to'   => null,
                    'production_date_from'   => null,
                    'production_date_to'     => null,
                    'id_organization'        => null,
                    'id_specialist'          => null,
                ];
                $rules = [
                    [
                        ['equipment_condition'],
                        'in',
                        'range'       => [
                            Balance::EQUIPMENT_CONDITION_WORK,
                            Balance::EQUIPMENT_CONDITION_NOT_WORK,
                        ],
                        'strict'      => true,
                        'skipOnEmpty' => true,
                        'skipOnError' => false
                    ],
                    [['id_organization', 'id_specialist'], 'integer'],
                    [['inventory_number', 'organization_name', 'manufactured_number', 'name'], 'string'],
                    [['inventory_number', 'organization_name', 'manufactured_number', 'name'], FullTrimValidator::class],
                    [['registration_date_from', 'registration_date_to', 'production_date_from', 'production_date_to'], 'date']
                ];
                break;
            default:
                throw new InvalidConfigException('Неизвестный тип ТМЦ');
        }

        // исключаем то что не описано в фильтре, добавляем требуемы поля
        $filter = array_intersect_key($filter, $empty_filter);
        $filter = array_merge($empty_filter, $filter);

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        // 403 по организации
        // Флаг allow_other_orgs, для функционала упрощённых вакцинаций есть доступы к балансам внешних организаций
        if (!empty($filter['id_organization']) && empty($filter['allow_other_orgs']) && !in_array($filter['id_organization'], $this->getUserOrgList())) {
            throw new ForbiddenHttpException('У вас нет доступа к указанной организации');
        }

        return $model->attributes;
    }

    /**
     * @param $type_tmc
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    protected function getQuery($type_tmc)
    {
        /**
         * Набор полей в ТМЦ зависит от его типа
         */
        $tmc_fields = TmcBase::getFieldsList($type_tmc);

        // Некоторые поля скрываем
        $exclude_fields = [
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'old_id',
        ];
        $tmc_fields = array_diff($tmc_fields, $exclude_fields);

        $balance_fields = Balance::getFieldsList($type_tmc);
        $balance_fields = array_diff($balance_fields, $exclude_fields);
        $balance_fields = array_map(function ($value) {
            return Balance::tableName() . '.' . $value;
        }, $balance_fields);

        /**
         * Запрос, общие поля и связи
         */
        $query = Balance::find()
            ->select($balance_fields)
            ->with([
                'organization' => function ($query) {
                    /* @var $query ActiveQuery */
                    $query
                        ->select([
                            'id',
                            'name',
                            'short_name',
                        ]);
                },
                'tmc' => function ($query) use ($tmc_fields) {
                    /* @var $query ActiveQuery */
                    $query
                        ->select($tmc_fields);
                },
            ])->innerJoin(
                'tmc.tmc',
                'tmc.balance.id_tmc = tmc.tmc.id 
                AND
                 tmc.balance.type_tmc = tmc.tmc.type'
            )
            ->where(['tmc.balance.type_tmc' => $type_tmc])
            ->orderBy('tmc.tmc.name ASC, tmc.balance.inventory_number')
            ->asArray()
        ;

        /**
         * Связи в зависимости от типа
         */
        if (in_array($type_tmc, [TmcBase::TYPE_EXP_MATERIAL, TmcBase::TYPE_VACCINE, TmcBase::TYPE_DRUG])) {
            $query->with([
                'specialist'      => function ($query) {
                    /* @var $query ActiveQuery */
                    $query
                        ->joinWith('user', false)
                        ->select(array_merge(['specialists.id'], Specialists::personalAttributes()));
                },
                'production_form' => function ($query) {
                    /* @var $query ActiveQuery */
                    $query
                        ->select([
                            'id',
                            'id_tmc',
                            'type_tmc',
                            'name',
                            'volume',
                            'is_utilize',
                            'is_deleted',
                        ]);
                },
                'tmc.categories'  => function ($query) {
                    /* @var $query ActiveQuery */
                    $query
                        ->select([
                            'id',
                            'name',
                            'description',
                        ]);
                },
                'tmc.measure'     => function ($query) {
                    /* @var $query ActiveQuery */
                    $query
                        ->select([
                            'measures.id',
                            'name',
                            'description',
                        ]);
                },
            ]);

        }

        if (in_array($type_tmc, [TmcBase::TYPE_VACCINE, TmcBase::TYPE_DRUG])) {
            $query->with([
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
                            'tmc.dosages.created_by',
                            'tmc.dosages.updated_by',
                            'tmc.dosages.created_at',
                            'tmc.dosages.updated_at',
                            //'tmc.dosages.age_range',
                            //'tmc.dosages.weight_range',
                        ]);
                }
            ]);
        }

        return $query;
    }

    /**
     * Список доступных пользователю организаций
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    protected function getUserOrgList()
    {
        if ($this->user_org_list === false) {

            $user = \Yii::$app->user->getIdentity();
            $id_organization = $user->specialist->id_organization;
            if ($id_organization == null) {
                throw new ForbiddenHttpException('Пользователь должен состоять в организации');
            }

            $this->user_org_list = (new Query())
                ->select('*')
                ->from(new Expression('tmc.get_org_tree_ids(:user_org)'))
                ->addParams([':user_org' => $id_organization])
                ->column();

            if (empty($this->user_org_list)) {
                throw new ForbiddenHttpException('У вас нет доступа к данному разделу');
            }
        }

        return $this->user_org_list;
    }
}
