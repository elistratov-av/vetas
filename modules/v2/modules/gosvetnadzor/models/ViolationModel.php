<?php


namespace app\modules\v2\modules\gosvetnadzor\models;


use app\common\components\inform\events\CancelViolationEvent;
use app\common\components\inform\events\PrimaryArvEvent;
use app\common\components\inform\events\PrimaryViolationOrderEvent;
use app\common\components\inform\events\SecondaryArvEvent;
use app\common\components\inform\events\SecondaryViolationOrderEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\events\VaccinationViolationEvent;
use app\common\components\inform\events\VetasLinkTest;
use app\common\components\inform\events\ViolationEvent;
use app\common\components\inform\SubscriptionService;
use app\common\models\VisitStatus;
use app\common\rabbit\SpkEventStatusConsumer;
use app\common\validators\FullTrimValidator;
use app\models\db\ContactTypes;
use app\models\db\Diseases;
use app\models\db\OrderType;
use app\models\db\PetIdentification;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetOwners;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\ServiceTypes;
use app\models\db\subscription\SubscriptionLog;
use app\models\db\tmc\TmcBase;
use app\models\db\Users;
use app\models\db\Violation;
use app\models\db\ViolationCancellation;
use app\models\db\ViolationType;
use app\models\db\Visits;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

class ViolationModel
{
    // Возможные шаблоны по оповещениям нарушений для метода notify()
    const VIOLATION_NOTIFICATION_TYPE_COMMON = 'violation';
    const VIOLATION_NOTIFICATION_TYPE_VACCINATION = 'vaccination_violation';
    const VIOLATION_NOTIFICATION_TYPE_LINK_TEST = 'link_test';

    // Статусы предписаний у нарушения
    const NO_ORDERS = 'no_orders';
    const PRIMARY_ORDERS = 'primary_orders';
    const SECONDARY_ORDERS = 'secondary_orders';
    const EXP_PRIMARY_ORDERS = 'expired_primary_orders';
    const EXP_SECONDARY_ORDERS = 'expired_secondary_orders';

    public $orderStatuses = [
        self::NO_ORDERS, self::PRIMARY_ORDERS, self::SECONDARY_ORDERS, self::EXP_PRIMARY_ORDERS, self::EXP_SECONDARY_ORDERS,
    ];

    // Дополнительные подфильтры по статусу нарушения "Отменено"
    const CANCELED_BY_ADMIN = 'canceled_by_admin';
    const CANCELED_AUTOMATICALLY = 'canceled_automatically';

    private $violationNotifications = [
        VaccinationViolationEvent::EVENT_CODE,
        ViolationEvent::EVENT_CODE,
        PrimaryViolationOrderEvent::EVENT_CODE,
        SecondaryViolationOrderEvent::EVENT_CODE,
        PrimaryArvEvent::EVENT_CODE,
        SecondaryArvEvent::EVENT_CODE,
        VetasLinkTest::EVENT_CODE,
    ];

    /**
     * Выдает список нарушений
     *
     * @param string $type
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @param array $with_status_counts
     * @return object|CommonList
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function getList(string $type, int $page = 1, int $limit = 10, array $filter = [], array $with_status_counts = [])
    {
        $filter  = $this->validateFilter($type, $page, $limit, $filter);

        $query = Violation::find()
            ->select('violation.id_violation, state, date_violation')
            ->leftJoin('violation_type', 'violation_type.id_type = violation.id_type')
            ->where(['violation_type.type' => $type]);

        $query = $this->applyFilter($query, $filter);

        $query_count = clone $query;

        $query
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->orderBy('date_violation DESC');

        return new CommonList(
            'violations',
            $query
                ->select('violation.*')
                ->with('pet')
                ->with('pet.species')
                ->with('pet.breeds')
                ->with('owner')
                ->with(['last_inspector' => function ($query) {
                    /** @var $query \yii\db\ActiveQuery */
                    $query->select([
                        'id',
                        'f_fio',
                        'i_fio',
                        'o_fio',
                        'fullname',
                        'birthday',
                        'sex',
                        'photo'
                    ]);
                }])
                ->with('owner.fias_addresses')
                ->with('owner.fact_fias_addresses')
                ->with('owner.fias_addresses.area')
                ->with('owner.fact_fias_addresses.area')
                ->distinct()
                ->asArray()
                ->all(),
            $query_count->distinct()->count(),
            $page,
            $limit,
            count($with_status_counts) > 0
                ? ['status_counts' => $this->getViolationCounts($type, $filter, $with_status_counts)]
                : ['status_counts' => []]
        );
    }

    /**
     * Выдаёт count для каждого типа status в фильтре
     *
     * @param string $type
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function getViolationCounts(string $type, array $filter = [], array $with_status_counts = []) {
        //Если передан $with_status_count используем его, в противном случае используем status из самого фильтра
        if (count($with_status_counts) > 0) {
            $filter['status'] = $with_status_counts;
        }
        if (!isset($filter['status']) || count($filter['status']) === 0) {
            throw new BadRequestHttpException('Не передан ни один статус');
        }
        $filter  = $this->validateFilter($type, 1, 1, $filter);
        $statuses = $filter['status'];
        unset($filter['status']);

        $subQuery = Violation::find()
            ->select('violation.id_violation as id')
            ->leftJoin('violation_type', 'violation_type.id_type = violation.id_type')
            ->where(['violation_type.type' => $type]);
        $subQuery = $this->applyFilter($subQuery, $filter);

        $select = '';
        foreach($statuses as $status) {
            $select .= "COUNT(v.id_violation) filter (where state = '$status') AS $status, ";
        }

        $query = (new \yii\db\Query())
            ->from('violation v')
            ->select($select)
            ->leftJoin('violation_type vt', 'vt.id_type = v.id_type')
            ->where(['vt.type' => $type])
            ->andWhere(['IN', 'v.id_violation', $subQuery])
        ;

        return array_change_key_case((array)$query->all()[0], CASE_UPPER);
    }

    /**
     * Получение лога отправленых нотификаций по конкретному нарушению
     *
     * @param $id
     * @return array
     */
    public function getNotificationLogList(int $id)
    {
        $query = (new \yii\db\Query())
            ->select([
                'sl.id',
                'sl.log_time date',
                'vh.description violation_type',
                'a.fullname author_fio',
                'is_unsubscribed_push',
                'is_unsubscribed_email',
                'click_count_push',
                'click_count_email',
                'status_email',
                'status_push',
            ])
            ->from('subscription.log as sl')
            ->where(['v.id_violation' => $id])
            ->andWhere(['sl.event_code' => $this->violationNotifications])
            ->andWhere(['sl.is_success' => true])
            ->leftJoin('public.users a', 'a.id = sl.id_author')
            ->leftJoin('public.violation v', 'v.id_violation = sl.id_violation')
            ->leftJoin('public.violation_history vh', 'vh.id_change = sl.id_violation_history')
            ->orderBy('log_time DESC')
        ;

        return $query->all();
    }

    /**
     * Редактирование нарушения
     *
     * @param $id
     * @param $id_type
     * @param $id_ARV
     * @param null $comment
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function editViolation($id, $id_type, $id_ARV, $comment = null)
    {
        $violation = Violation::findOne(['id_violation' => $id]);

        if (empty($violation)) {
            throw new BadRequestHttpException('Указанное нарушение не найдено');
        }

        if ($violation->isReadOnly()) {
            throw new BadRequestHttpException('Указанное нарушение недоступно для редактирования');
        }

        $violation->id_type = $id_type;
        $violation->id_ARV = $id_ARV;
        $violation->comment = $comment;

        Violation::getDb()->beginTransaction();

        /*
         * Пишем в историю
         */
        (new ViolationHistoryModel)
            ->addRecordAboutEdit($violation);

        if (!$violation->save()) {
            $errors = $violation->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении нарушения' : implode("\n", array_values($errors)));
        }

        Violation::getDb()->transaction->commit();

        return true;
    }


    /**
     * Просмотр нарушения
     *
     * @param $id
     * @param bool $withInformStatus
     * @return array|\yii\db\ActiveRecord|null
     * @throws BadRequestHttpException
     */
    public function getViolation($id, $withInformStatus = false)
    {
        $violation = Violation::find()
            ->with('owner')
            ->with('owner.fias_addresses')
            ->with('owner.fact_fias_addresses')
            ->with('owner.fias_addresses.area')
            ->with('owner.fact_fias_addresses.area')
            ->with('owner.contacts')
            ->with('owner.contacts.contact_type')
            ->with('pet')
            ->with('pet.breeds')
            ->with('pet.species')
            ->with(['pet.pet_rabies_vaccinations' => function ($query) {
                /** @var $query \yii\db\ActiveQuery */
                $query->orderBy('date DESC');
            }])
            ->with('files')
            ->with('violation_admin_rights')
            ->with('cancellation')
            ->with('type')
            ->with(['orders' => function ($query) {
                $query
                    ->with(['arv' => function ($query) {
                        $query
                            ->with(['violation_admin_rights'])
                            ->orderBy('id');
                    }])
                    ->orderBy('id_order');
            }])
            ->with(['owner_feedback' => function ($query) {
                $query
                    ->with([
                        'files',
                        'organization',
                        'tmc',
                    ])
                    ->orderBy(['id' => SORT_DESC])->one();
            }])
            ->asArray()
            ->where([
                'id_violation' => $id
            ])
            ->one()
        ;

        if (empty($violation)) {
            throw new BadRequestHttpException('Указанное нарушение не найдено');
        }

        $result = $violation;

        if ($withInformStatus) {
            try {
                // VETAIS-2427
                //$result['need_inform'] = (PetOwners::findOne(['id' => $violation['id_owner']]))->hasSubscriptions();
                $result['need_inform'] = false;
            } catch (\Exception $e) {
                $result['need_inform'] = false;
            }
        }

        return $result;
    }

    /**
     * Применяет фильтр в зависимости от входных параметров
     *
     * @param ActiveQuery $query
     * @param array $filter
     * @return ActiveQuery
     */
    protected function applyFilter($query, $filter)
    {
        if (!empty($filter['status'])) {
            $sqlStatus = ['or'];

            $cancelByAdmin = in_array(self::CANCELED_BY_ADMIN, $filter['status']);
            $cancelAutomatically = in_array(self::CANCELED_AUTOMATICALLY, $filter['status']);

            $filter['status'] = array_filter($filter['status'], function ($status) {
                return !in_array($status, [self::CANCELED_BY_ADMIN, self::CANCELED_AUTOMATICALLY]);
            });

            if ($cancelByAdmin && $cancelAutomatically) {
                array_push($filter['status'], Violation::STATE_CANCELED);
            } else if ($cancelByAdmin || $cancelAutomatically) {
                /** @var ViolationCancellation[] $automaticCancelReasons */
                $automaticCancelReasons = ViolationCancellation::find()->where([
                    'OR',
                    ['NOT', ['tech_name' => null]],
                    ['NOT IN', 'tech_name', ViolationCancellation::PUBLIC_TECH_NAMES]
                ])->all();

                $reasonMessages = [
                    'Автоматическая отмена нарушения для животного снятого с учета',
                    'Автоматическая отмена нарушения для животного помеченого как дубль',
                ];
                foreach($automaticCancelReasons as $reason) {
                    array_push($reasonMessages, $reason->description);
                }

                $query->leftJoin('violation_history as vh', ['and',
                    'vh.id_violation = violation.id_violation',
                    ['in', 'vh.description', $reasonMessages]
                ]);

                if ($cancelByAdmin) array_push($sqlStatus, ['and', ['vh' => null, 'state' => Violation::STATE_CANCELED]]);
                if ($cancelAutomatically) array_push($sqlStatus, ['and', ['NOT', ['vh' => null]], ['state' => Violation::STATE_CANCELED]]);
            }

            if (count($filter['status']) > 0) {
                array_push($sqlStatus, ['in', 'state', $filter['status']]);
            }

            $query->andWhere($sqlStatus);
        }

        if (!empty($filter['id_species'])) {
            $query->leftJoin('pets', 'pets.id = violation.id_pet');
            $query->andWhere([
                'pets.id_species' => $filter['id_species']
            ]);
        }

        /*
         * Нужна таблица pet_owners
         */
        if (!empty($filter['pet_owner_name']) || !empty($filter['id_area'])){
            $query->leftJoin('pet_owners', 'pet_owners.id = violation.id_owner');
        }

        if (!empty($filter['pet_owner_name'])) {
            $query->andWhere([
                'ILIKE', 'pet_owners.fullname', $filter['pet_owner_name']
            ]);
        }

        if (!empty($filter['id_area'])) {
            $query
                ->leftJoin('fias_addresses', 'pet_owners.id_fias_address = fias_addresses.id')
                ->leftJoin('fias_addresses AS fact_fias_addresses', 'pet_owners.id_fact_fias_address = fact_fias_addresses.id')
                ->andWhere([
                    /*
                     * в поиске должны использоваться один адрес из тех что есть у владельца
                     * pet_owners.id_fias_address, pet_owners.id_fact_fias_address (параметры приведен в порядке приоритетности).
                     * То есть по умолчанию ищем по адресу pet_owners.id_fias_address,
                     * но если его нет тогда по -  pet_owners.id_fact_fias_address
                     */
                    'OR',
                    ['fias_addresses.id_area' => $filter['id_area']],
                    [
                        'AND',
                        ['fias_addresses.id_area' => null],
                        ['fact_fias_addresses.id_area' => $filter['id_area']]
                    ],
                ]);
        }

        if (!empty($filter['id_violation_type'])) {
            $query->andWhere([
                'violation.id_type' => $filter['id_violation_type']
            ]);
        }

        if (!empty($filter['violation_date_from'])) {
            $query->andWhere([
                '>=', 'violation.date_violation', $filter['violation_date_from']
            ]);
        }

        if (!empty($filter['violation_date_to'])) {
            $query->andWhere([
                /*
                 * Включая текущий день
                 *  до 23:59:59
                 */
                '<=', 'violation.date_violation', $filter['violation_date_to'] . ' 23:59:59'
            ]);
        }

        if (!empty($filter['inspector_name'])) {
            $query->leftJoin('violation_history', '
            violation_history.id_change = (
            SELECT 
                violation_history.id_change 
            FROM violation_history
            WHERE 
                violation.id_violation = violation_history.id_violation
            ORDER BY date DESC
            LIMIT 1)
            ');

            $query->leftJoin('users', 'violation_history.id_inspector = users.id');
            $query->andWhere([
                'ILIKE', 'users.fullname', $filter['inspector_name']
            ]);
        }


        if (!empty($filter['only_with_fio_and_address'])) {
            $query->andWhere('pet_owners.fullname IS NOT NULL AND pet_owners.id_fact_fias_address IS NOT NULL')
                  ->andWhere('pet_owners.fullname IS NOT NULL AND pet_owners.id_fias_address IS NOT NULL');
        }

        if (!empty($filter['only_without_fio_and_address'])) {
            $query->andWhere('pet_owners.fullname IS NULL OR pet_owners.id_fact_fias_address IS NULL OR pet_owners.id_fias_address IS NULL');
        }

        $query->leftJoin('order o', 'violation.id_violation = o.id_violation');
        $query->leftJoin('violation_ARV arv', 'o.id_order = arv.id_order');

        if (!empty($filter['order_number'])) {
            $query->andWhere([
                'ILIKE', 'o.number', $filter['order_number']
            ]);
        }

        if (!empty($filter['order_date_from'])) {
            $query->andWhere([
                '>=', 'o.date_order', $filter['order_date_from']
            ]);
        }

        if (!empty($filter['order_date_to'])) {
            $query->andWhere([
                '<=', 'o.date_order', $filter['order_date_to']
            ]);
        }

        if (!empty($filter['order_expire_date_from'])) {
            $query->andWhere([
                '>=', 'o.date_to', $filter['order_expire_date_from']
            ]);
        }

        if(!empty($filter['order_expire_date_to'])) {
            $query->andWhere([
                '<=', 'o.date_to', $filter['order_expire_date_to']
            ]);
        }

        if (!empty($filter['arv_date_from'])) {
            $query->andWhere([
                '>=', 'arv.date_ARV', $filter['arv_date_from']
            ]);
        }

        if (!empty($filter['arv_date_to'])) {
            $query->andWhere([
                '<=', 'arv.date_ARV', $filter['arv_date_to']
            ]);
        }

        if (!empty($filter['with_arv']) && !empty($filter['arv_type'])) {
            $query->andWhere([
                '=', 'arv.id_ARV', $filter['arv_type']
            ]);
        }

        if (!empty($filter['with_arv'])) {
            $query->innerJoin('violation_ARV arvo', 'o.id_order = arvo.id_order');
        }

        if (!empty($filter['declined_by_owner'])) {
            $query->innerJoin('violation_history vh', "violation.id_violation = vh.id_violation and vh.description like '%Отказ владельца%'");
        }

        if (!empty($filter['planned_visit'])) {
            $query->innerJoin('visits_gov_services as vgs', 'vgs.id_pet = violation.id_pet')
                ->leftJoin('visit_pets as vp', 'vp.id_pet = violation.id_pet')
                ->leftJoin('visits as v', 'v.id = vp.id_visit')
                ->leftJoin('gov_services as gs', 'vgs.id_service = gs.id')
                ->leftJoin('violation_type as vt', 'vt.id_type = violation.id_type');
            $query->andWhere("(vt.type = '".ViolationType::TYPE_VACCINATION_VIOLATION."' AND gs.id_service_type = '".ServiceTypes::TYPE_VACCINATION."') 
                            OR (vt.type = '".ViolationType::TYPE_IDENT_VIOLATION."' AND gs.id_service_type = '".ServiceTypes::TYPE_IDENTIFICATION."')")
                ->andWhere("v.status != '".VisitStatus::CANCELED."'")
                ->andWhere('upper(v.time_range)::date >= CURRENT_TIMESTAMP');
        }

        if (!empty($filter['planned_close'])) {
            $rabies_id = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one()->id;
            $leptospiroz_id = Diseases::find()->where(['name' => Diseases::NAME_LEPTOSPIROZ])->one()->id;

            $query->leftJoin('pets as p', 'p.id = violation.id_pet')
                ->leftJoin('violation_type as vt1', 'vt1.id_type = violation.id_type');
            $query->andWhere("(violation.date_plan >= now())
                OR (vt1.type = '".ViolationType::TYPE_VACCINATION_VIOLATION."' AND violation.id_disease = $rabies_id AND p.date_plan_rabies_vaccination >= now())
                OR (vt1.type = '".ViolationType::TYPE_VACCINATION_VIOLATION."' AND violation.id_disease = $leptospiroz_id AND p.date_plan_lept_vaccination >= now())
                OR (vt1.type = '".ViolationType::TYPE_IDENT_VIOLATION."' AND p.date_plan_identification >= now())");
        }

        if (!empty($filter['owner_with_no_contacts'])) {
            $query->leftJoin('contacts as c', "c.entity_type = 'pet_owner' AND c.entity_id = violation.id_owner")
                ->leftJoin('pet_owners as po', 'po.id = violation.id_owner');
            $query->andWhere('c IS NULL AND po.id_fact_fias_address IS NULL AND po.id_fias_address IS NULL');
        }

        if (!empty($filter['created_automatically'])) {
            $query->andWhere([
                'LIKE', 'violation.comment', 'Автоматическая проверка на наличие'
            ]);
        }

        if (!empty($filter['with_owner_feedback'])) {
            $query->innerJoin('owner_feedback as of', 'violation.id_violation = of.id_violation');
        }

        if (!empty($filter['order_status'])) {
            $sql = ['or'];
            $query->leftJoin('order_type as ot', 'ot.id_type = o.id_type');

            if (in_array(self::NO_ORDERS, $filter['order_status'])) {
                array_push($sql, ['o' => null]);
            }

            if (in_array(self::PRIMARY_ORDERS, $filter['order_status'])) {
                array_push($sql, ['=', 'ot.name', OrderType::TYPE_PRIMARY]);
            }

            if (in_array(self::SECONDARY_ORDERS, $filter['order_status'])) {
                array_push($sql, ['=', 'ot.name', OrderType::TYPE_SECONDARY]);
            }

            $expPrimaryFilter = in_array(self::EXP_PRIMARY_ORDERS, $filter['order_status']);
            $expSecondaryFilter = in_array(self::EXP_SECONDARY_ORDERS, $filter['order_status']);
            if ($expPrimaryFilter || $expSecondaryFilter) {
                $query->leftJoin('order as exp_or', 'exp_or.id_violation = violation.id_violation AND exp_or.date_to < now()');

                $orderType = $expPrimaryFilter && !$expSecondaryFilter ? OrderType::TYPE_PRIMARY : null;
                $orderType = $expSecondaryFilter && !$expPrimaryFilter ? OrderType::TYPE_SECONDARY : $orderType;

                if ($orderType) {
                    $query->leftJoin('order_type as exp_or_type', 'exp_or_type.id_type = exp_or.id_type');
                    array_push($sql, ['=', 'exp_or_type.name', $orderType]);
                } else {
                    array_push($sql, ['NOT', ['exp_or' => null]]);
                }
            }

            $query->andWhere($sql);
        }

        return $query;
    }

    /**
     * Валидирует входные параметры
     *
     * @param $type
     * @param $page
     * @param $limit
     * @param $filter
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateFilter($type, $page, $limit, $filter)
    {
        if (!is_numeric($page) || $page < 1) {
            throw new BadRequestHttpException('Параметр page некорректный');
        }

        if (!is_numeric($limit) || $limit < 0) {
            throw new BadRequestHttpException('Параметр limit некорректный');
        }

        $allowed_types = [
            ViolationType::TYPE_IDENT_VIOLATION,
            ViolationType::TYPE_OTHER_VIOLATION,
            ViolationType::TYPE_VACCINATION_VIOLATION,
        ];

        if (!in_array($type, $allowed_types)) {
            throw new BadRequestHttpException('Указан неизвестный тип');
        }

        if (empty($filter)) {
            return;
        }

        $empty_filter = [
            'inspector_name' => null, 'pet_owner_name' => null, 'id_species' => null,
            'id_violation_type' => null, 'violation_date_from' => null, 'violation_date_to' => null,
            'status' => null, 'id_area' => null,
            'order_status' => null, 'order_number' => null, 'order_date_from' => null, 'order_date_to' => null,
            'order_expire_date_from' => null, 'order_expire_date_to' => null, 'with_arv' => null, 'arv_type' => null,
            'arv_date_from' => null, 'arv_date_to' => null, 'with_owner_feedback' => null, 'planned_visit' => null,
            'planned_close' => null, 'declined_by_owner' => null, 'owner_with_no_contacts' => null, 'created_automatically' => null,
        ];

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['id_species', 'id_violation_type', 'id_area', 'arv_type'], 'integer'],
            [['inspector_name', 'pet_owner_name', 'order_number'], 'string'],
            [['inspector_name', 'pet_owner_name', 'order_number'], FullTrimValidator::class],
            [['violation_date_from',
                'violation_date_to',
                'order_date_from',
                'order_date_to',
                'order_expire_date_from',
                'order_expire_date_to',
                'arv_date_from',
                'arv_date_to'], 'date'],
            [['with_owner_feedback',
                'planned_visit',
                'planned_close',
                'declined_by_owner',
                'owner_with_no_contacts',
                'created_automatically',
                'with_arv'], 'boolean'],
            [['status'], 'each',
                'rule' => ['in', 'range' => [
                    Violation::STATE_NEW,
                    Violation::STATE_ON_VERIFY,
                    Violation::STATE_IN_WORK,
                    Violation::STATE_CANCELED,
                    Violation::STATE_FINISHED,
                    Violation::STATE_ACCEPTED,
                    self::CANCELED_BY_ADMIN,
                    self::CANCELED_AUTOMATICALLY,
                ]]
            ],
            [['order_status'], 'each',
                'rule' => ['in', 'range' => $this->orderStatuses]]
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        return $model->attributes;
    }

    /**
     * Отправка уведомления
     *
     * @param int $id
     * @param string $templateType
     * @param string|null $comment
     * @param string|null $expDate
     * @param array|null $fileIds
     * @return bool
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function notify(int $id, $templateType, $comment, $expDate, $fileIds)
    {
        $violation = Violation::findOne(['id_violation' => $id]);

        if (empty($violation)) {
            throw new BadRequestHttpException('Указанное нарушение не найдено');
        }

        if ($violation->state != Violation::STATE_IN_WORK) {
            throw new BadRequestHttpException('Неверный статус нарушения');
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($violation->owner, [ ContactTypes::TYPE_EMAIL ]);
        if (empty($contacts)) {
            throw new BadRequestHttpException('У пользователя нет подписок на уведомление');
        }

        $notificationHistory = [];
        foreach ($contacts as $contact) {
            $description = 'Владельцу отправлено уведомление о нарушении на email: ' . $contact->name;

            /*
             * VETAIS-2243 - Пишем в историю
             */
            $violationHistory = (new ViolationHistoryModel)
                ->addRecordAboutSendNotify($violation, $description);

            $notificationHistory[$contact->id] = $violationHistory->id_change;
        }

        if ($templateType === self::VIOLATION_NOTIFICATION_TYPE_VACCINATION) {
            // Так как метод пока универсален для разных нарушений, провалидируем наличие параметра ручками
            if (!$expDate)
                throw new BadRequestHttpException("Не определён exp_date для оповещения типа: " . self::VIOLATION_NOTIFICATION_TYPE_VACCINATION);
            if (!$violation->feedback_token) $this->generateNewFeedbackToken($violation);

            \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new VaccinationViolationEvent([
                'contacts' => $contacts,
                'date_exp' => $expDate,
                'violation' => $violation,
                'id_visit' => $violation->id_visit,
                'id_pet' => $violation->id_pet,
                'id_author' => \Yii::$app->user->id,
                'files_token' => $fileIds ? $this->generateFilesToken($violation) : null,
                'attached_files_ids' => $fileIds,
                'notification_history' => $notificationHistory,
            ]));
        }
        if ($templateType === self::VIOLATION_NOTIFICATION_TYPE_COMMON) {
            // Так как метод пока универсален для разных нарушений, провалидируем наличие параметра ручками
            if (!$comment)
                throw new BadRequestHttpException("Не определён comment для оповещения типа: ".self::VIOLATION_NOTIFICATION_TYPE_COMMON);
            \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new ViolationEvent([
                'contacts' => $contacts,
                'text' => nl2br($comment),
                'violation' => $violation,
                'id_pet' => $violation->id_pet,
                'id_visit' => $violation->id_visit,
                'id_author' => \Yii::$app->user->id,
                'files_token' => $fileIds ? $this->generateFilesToken($violation) : null,
                'attached_files_ids' => $fileIds,
                'notification_history' => $notificationHistory,
            ]));
        }
        // Шаблон для тестирования получения статистики по переходам по ссылке
        if ($templateType === self::VIOLATION_NOTIFICATION_TYPE_LINK_TEST) {
            \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new VetasLinkTest([
                'contacts' => $contacts,
                'violation' => $violation,
                'files_token' => $this->generateFilesToken($violation),
                'attached_files_ids' => $fileIds,
                'notification_history' => $notificationHistory,
            ]));
        }

        return true;
    }

    /**
     * Отправка уведомления о закрытии нарушения (отправляется системой)
     * Производится лишь в случае, если предварительно было отправлено и доставлено сообщение о нарушении
     *
     * @param Violation $violation
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function notifyClosedViolation(Violation $violation)
    {
        if (!$violation->isFinished()) {
            throw new BadRequestHttpException('Неверный статус нарушения');
        }
        $contacts = SubscriptionService::getOwnerSubscriptions($violation->owner, [ ContactTypes::TYPE_EMAIL ]);

        if (!empty($contacts)) {
            $violationEvents = [
                ViolationEvent::EVENT_CODE,
                VaccinationViolationEvent::EVENT_CODE,
            ];

            $notification = SubscriptionLog::find()
                ->where(['AND',
                    ['id_violation' => $violation->id_violation],
                    ['is_success' => true],
                    ['event_code' => $violationEvents],
                    ['status_email' => SpkEventStatusConsumer::$deliveredActions]
                ])->one();

            if ($notification) {
                $systemUserId = Users::find()->where('is_system_user IS TRUE')->one()->id;
                $notificationHistory = [];
                foreach ($contacts as $contact) {
                    $description = 'Владельцу отправлено уведомление о закрытии нарушения на email: ' . $contact->name;

                    $violationHistory = (new ViolationHistoryModel)
                        ->addRecordAboutSendViolationClosedNotify($violation, $description, $systemUserId);

                    $notificationHistory[$contact->id] = $violationHistory->id_change;
                }

                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new CancelViolationEvent([
                    'contacts' => $contacts,
                    'violation' => $violation,
                    'id_pet' => $violation->id_pet,
                    'id_visit' => $violation->id_visit,
                    'notification_history' => $notificationHistory,
                ]));
            }
        }
    }

    /**
     * Проверка, что животное имеет нарушение по идентификации и оно закрывается если есть идентификатор.
     * Вызывается при добавлении идентификации в карточке животного.
     *
     * @param array $petIds
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function checkAndCancelIdentificationViolation(array $petIds){
        foreach ($petIds as $id){
            /** @var Violation $violation */
            $violation = Violation::find()
                ->leftJoin('violation_type vt', 'vt.id_type = violation.id_type')
                ->where([
                    'AND',
                    ['id_pet' => $id],
                    ['IN', 'state', Violation::ACTIVE_STATES],
                    ['=', 'vt.type', ViolationType::TYPE_IDENT_VIOLATION],
                ])
                ->one();

            if ($violation) {
                $ident_exists = PetIdentification::find()->where(['id_pet' => $id])->exists();
                //Нарушение закрывается если есть идентификатор и у нарушения нет АПН.
                if ($ident_exists && count($violation->arvs) === 0) {
                    /** @var ViolationCancellation $cancelViolation */
                    $cancelViolation = ViolationCancellation::find()->where(['tech_name' => ViolationCancellation::CANCEL_BY_IDENTIFICATION])->one();
                    (new ViolationChangeStateModel(['id_violation' => $violation->id_violation]))->cancel($cancelViolation->id_cancellation);
                }
            }
        }
    }

    /**
     * Проверка, что животное имеет нарушение по вакцинации от бешенства и оно было закрыто одной из существующих вакцин
     * Вызывается при обновлении списка вакцин животного в карте животного или осмотре
     *
     * @param array $petIds
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function checkAndCancelPetsRabiesViolation(array $petIds)
    {
        /** @var Diseases $rabies */
        $rabies = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one();

        foreach ($petIds as $id) {
            /** @var Violation $violation */
            $violation = Violation::find()
                ->leftJoin('violation_type vt', 'vt.id_type = violation.id_type')
                ->where([
                    'AND',
                    ['id_pet' => $id],
                    ['IN', 'state', Violation::ACTIVE_STATES],
                    ['=', 'vt.type', ViolationType::TYPE_VACCINATION_VIOLATION],
                    ['=', 'id_disease', $rabies->id]
                ])->one();

            if ($violation) {
                /** @var Pets $pet */
                $pet = Pets::find()->where(['id' => $id])->one();
                $vaccines = $pet->pet_rabies_vaccinations;
                if ($pet->is_main === true && $pet->duplicates) {
                    foreach ($pet->duplicates as $double) {
                        $vaccines = array_merge($vaccines, $double->pet_rabies_vaccinations);
                    }
                }
                foreach ($vaccines as $vaccination) {
                    // Нарушение отменяется, если есть действующая вакцина и у нарешения ещё нет Административно Правовых Нарушений (АПН)
                    if ($vaccination->vaccine->isDiseasesRabies()
                        && date_create_from_format('Y-m-d', $vaccination->valid_until) > date('Y-m-d')
                        && count($violation->arvs) === 0) {

                        /** @var ViolationCancellation $cancelViolation */
                        $cancelViolation = ViolationCancellation::find()->where(['tech_name' => ViolationCancellation::CANCEL_BY_VACCINATION])->one();
                        (new ViolationChangeStateModel(['id_violation' => $violation->id_violation]))->cancel($cancelViolation->id_cancellation);

                        break;
                    }
                }
            }
        }
    }

    /**
     * @param Violation $violation
     */
    public function generateNewFeedbackToken(Violation $violation)
    {
        $token = md5($violation->id_violation . microtime());
        $violation->feedback_token = $token;
        $violation->save();
    }

    /**
     * @param Violation $violation
     * @return bool
     */
    public function hasPlannedToClose(Violation $violation)
    {
        if ($violation->date_plan
            && new \DateTime() < \DateTime::createFromFormat('Y-m-d', $violation->date_plan)) {
            return true;
        }
        switch($violation->type->type) {
            case ViolationType::TYPE_VACCINATION_VIOLATION:
                $serviceType = ServiceTypes::TYPE_VACCINATION;
                break;
            case ViolationType::TYPE_IDENT_VIOLATION:
                $serviceType = ServiceTypes::TYPE_IDENTIFICATION;
                break;
        }
        if (!empty($serviceType)) {
            $visits = Visits::find()
                ->leftJoin('visits_gov_services as vgs', 'visits.id_pet = vgs.id_pet')
                ->leftJoin('gov_services as gs', 'vgs.id_service = gs.id')
                ->where("visits.id_pet = $violation->id_pet")
                ->andWhere('lower(visits.time_range) >= CURRENT_TIMESTAMP')
                ->andWhere("gs.id_service_type = $serviceType")
                ->andWhere("visits.status != '".VisitStatus::CANCELED."'")
                ->all()
            ;
            if (count($visits) !== 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Violation $violation
     * @return bool
     */
    public function isViolationClosed(Violation $violation)
    {
        $isClosed = false;
        switch ($violation->type->type) {
            case ViolationType::TYPE_IDENT_VIOLATION:
                $identificationCount = PetIdentification::find()->where(['id_pet' => $violation->id_pet])->count();
                $isClosed = $identificationCount > 0;
                break;
            case ViolationType::TYPE_VACCINATION_VIOLATION:
                $isClosed = $this->hasActiveVaccination($violation);
                break;
        }
        return $isClosed;
    }

    /**
     * @param Violation $violation
     * @return bool
     * @throws Exception
     */
    public function hasActiveVaccination(Violation $violation)
    {
        $vaccineType = $this->getViolationVaccineType($violation);
        $vaccineCount = $vaccineType->find()
            ->leftJoin('tmc.tmc as tmc', $vaccineType::tableName().".id_vaccine = tmc.id")
            ->leftJoin('tmc.tmc_to_diseases as ttd', 'tmc.id = ttd.id_tmc')
            ->where('valid_until > now()')
            ->andWhere("ttd.id_disease = ".$violation->id_disease)
            ->andWhere($vaccineType::tableName().".type_tmc = '".TmcBase::TYPE_VACCINE."'")
            ->andWhere("id_pet = ".$violation->id_pet)
            ->count();

        return $vaccineCount > 0;
    }

    /**
     * @param Violation $violation
     * @return string
     */
    private function generateFilesToken(Violation $violation)
    {
        return md5($violation->id_violation . microtime());
    }

    /**
     * @param Violation $violation
     * @return PetOtherVaccinations|PetRabiesVaccination
     * @throws Exception
     */
    private function getViolationVaccineType(Violation $violation)
    {
        if ($violation->type->type !== ViolationType::TYPE_VACCINATION_VIOLATION) {
            throw new Exception('Передан неверный тип нарушения');
        }

        /** @var Diseases $rabies */
        $rabies = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one();
        switch ($violation->id_disease) {
            case $rabies->id:
                $vaccineType = (new PetRabiesVaccination);
                break;
            default:
                $vaccineType = (new PetOtherVaccinations);
                break;
        }
        return $vaccineType;
    }
}
