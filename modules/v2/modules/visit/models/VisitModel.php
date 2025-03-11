<?php

namespace app\modules\v2\modules\visit\models;

use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\events\TransferVisitEvent;
use app\common\components\inform\SubscriptionService;
use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\TmpPetOwners;
use app\models\db\Visits;
use app\models\db\VisitsGovServices;
use app\modules\admin\models\GovServices;
use app\modules\v2\modules\visit\skeletons\visit\Lists;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\queue\db\Queue;
use yii\web\BadRequestHttpException;
use yii\db\Query;

class VisitModel
{
    /**
     * @see https://jira.altarix.ru/browse/VETAIS-911
     * @param int $idVisit
     * @return array
     * @throws BadRequestHttpException
     */
    public function referrals(int $idVisit): array
    {
        $result = Visits::find()
            ->select([
                'id',
                'author',
                'source'
            ])
            ->where(['id' => $idVisit])
            ->with(['author_ref' => function ($sq1) {
                /* @var $sq1 \yii\db\ActiveQuery */
                $sq1->joinWith('user', false)
                    ->select(array_merge(['specialists.*'], Specialists::personalAttributes()));
            }])
            ->with(['source_ref' => function ($query) {
                /** @var $query ActiveQuery * */
                //$query
            }])
            ->with(['child_visits' => function ($query) {
                /** @var $query ActiveQuery * */
                $query
                    ->with('services')
                    ->with('organization')
                    ->with(['specialists' => function ($sq2) {
                        /* @var $sq2 \yii\db\ActiveQuery */
                        $sq2->joinWith('user', false)
                            ->select(array_merge(['specialists.*'], Specialists::personalAttributes()));
                    }]);
            }])
            ->asArray()
            ->one();

        if (empty($result)) {
            throw new BadRequestHttpException('Указанный прием не найден');
        }

        return $result;
    }

    /**
     * Возвращает массив слотов и визитов для страницы распичание специалиста
     * @param int $idSpecialist
     * @param int $idOrganization
     * @param string $dateFrom
     * @return array
     * @throws \yii\db\Exception
     * @todo ПЕРЕПИСАТЬ!!!
     *
     */
    public function specialistWorkday(int $idSpecialist, int $idOrganization, string $dateFrom, $serviceTypeIds = []): array
    {
        $dates = $this->convertIntervalToDates($dateFrom, 1);
        $slots = $this->selectSpecialistWorkdaySlots($idSpecialist, $dates[0], $dates[1], $serviceTypeIds);

        $result = [];

        foreach ($slots as &$slot) {

            $result[$slot['slot']]['slot'] = substr($slot['slot'], 0, -3);

            if (!array_key_exists('visits', $result[$slot['slot']])) {
                $result[$slot['slot']]['visits'] = NULL;
            }

            if (empty($slot['id_visit'])) {
                continue;
            }

            $result[$slot['slot']]['visits'][] = [
                'id_visit' => $slot['id_visit'],
                'id_shift_type' => $slot['id_shift_type'],
            ];
        }

        return array_values($result);
    }

    /**
     * не учитываем приёмы с ЖО
     * @see https://jira.altarix.ru/browse/VETAIS-1875
     *
     * @param int $idSpecialist
     * @param string $strDateTimeFrom
     * @param string $strDateTimeTo
     * @return array
     * @throws \yii\db\Exception
     */
    public function selectSpecialistWorkdaySlots(int $idSpecialist, string $strDateTimeFrom, string $strDateTimeTo, array $serviceTypeIds = []): array
    {
        if (!empty($serviceTypeIds)) {
            $joinServiceString = 'LEFT JOIN visits_gov_services ON visits_gov_services.id_visit = visits_specialists.id_visit ';
            $joinServiceString .= 'LEFT JOIN gov_services ON gov_services.id = visits_gov_services.id_service';
            $whereServiceString = 'AND gov_services.id_service_type IN (' . implode(", ", $serviceTypeIds) . ')';
        } else {
            $joinServiceString = '';
            $whereServiceString = '';
        }

        $sql = <<<SQL
SELECT DISTINCT
       --work_time_slots.id_specialist,
       slot::time,
       --slot_range,
       visits.id AS id_visit,
       --visits.time_range,
       visits.channel AS id_shift_type
FROM (SELECT
             id_specialist,
             slot,
             tsrange(slot, slot + '10 minutes' :: interval, '[)' :: text) AS slot_range
      FROM (
           -- Находим рабочий диапазон в указанном дне
           -- Генерируем последователность слотов
           SELECT timesheets.date,
                   timesheets.id_specialist,
                   generate_series(
                      lower(timesheets.date),
                      (upper(timesheets.date) - '00:10:00' :: interval),
                      '00:10:00' :: interval
                   ) AS slot
            FROM timesheets
                   JOIN shifts on timesheets.id_shift = shifts.id
                   JOIN shift_type on shifts.id_type = shift_type.id
            WHERE
                shift_type.type = 'WORKDAY'
              AND
                id_specialist = :id_specialist
              AND
                (
                    timesheets.date = tsrange(:date_from, :date_to, '[)')     -- равен
                      OR
                    timesheets.date && tsrange(:date_from, :date_to, '[)')    -- имеет общие точки
                )

           ) AS work_slots

     ) AS work_time_slots
LEFT JOIN visits_specialists
         ON visits_specialists.id_specialist = work_time_slots.id_specialist
$joinServiceString
    LEFT JOIN visits ON 
  visits_specialists.id_visit = visits.id 
  AND
(
    work_time_slots.slot_range = visits.time_range     -- равен
      OR
    work_time_slots.slot_range && visits.time_range    -- имеет общие точки
)
AND 
  (visits.status NOT IN ('A', 'T') AND visits.channel != :livequeue_channel)
WHERE
    (
        work_time_slots.slot_range = tsrange(:date_from, :date_to, '[)')     -- равен
          OR
        work_time_slots.slot_range && tsrange(:date_from, :date_to, '[)')    -- имеет общие точки
        )
$whereServiceString
ORDER BY slot ASC
SQL;

        return Visits::getDb()
            ->createCommand($sql, [
                ':id_specialist' => $idSpecialist,
                ':date_from' => $strDateTimeFrom,
                ':date_to' => $strDateTimeTo,
                ':livequeue_channel' => ShiftType::getTypesIdWithTimeLiveQueue(),
            ])
            ->query()
            ->readAll();
    }

    /**
     * Получение списка визитов
     *
     * @param int $idOrganization
     * @param array|int|null $idShiftType
     * @param string $dateFrom
     * @param int $daysCount
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return Lists
     * @throws BadRequestHttpException
     */
    public function list(int $idOrganization, $idShiftType, string $dateFrom, int $daysCount = 1,
                         int $page = 1, int $limit = 10, array $filter = []): Lists
    {
        [$strDateTimeFrom, $strDateTimeTo, $flagTimeNow] = $this->convertIntervalToDates($dateFrom, $daysCount);
        $dateExpression = $this->prepareDateExpressionTimeRange($strDateTimeFrom, $strDateTimeTo);
        $query = $this->prepareQuery($idOrganization, $filter, $idShiftType, $dateExpression);
        if ($this->checkShiftTypesContainsLiveQueue($idShiftType)) {
            $idShiftTypeLiveQueue = ShiftType::find()->select(['id'])->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE])->one();
            $startDateExpression = $this->prepareStartDateExpression($strDateTimeFrom, $strDateTimeTo);
            $visitsLiveQueue = $this->prepareQuery($idOrganization, $filter, $idShiftTypeLiveQueue->id, $startDateExpression);
            $query = $query->union($visitsLiveQueue);
        }

        $visits = $this->executeSelectWithOrder($query);

        $result = new Lists($visits, \count($visits));
        foreach ($result->visits as &$item) {
            $rows = (new \yii\db\Query())
                ->select(['read'])
                ->from('notifications')
                ->where([
                    'id_visit' => $item['id'],
                ])
                ->all();
            if (count($rows) > 0) {
                $item["read_notification"] = $rows[0]['read'];
            } else {
                $item["read_notification"] = null;
            };
        }
        $result->customPagination($page, $limit);
        return $result;
    }

    /**
     * @param Visits $visit
     * @return bool
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function notify(Visits $visit)
    {
        if (!in_array($visit->status, [VisitStatus::NEW, VisitStatus::CHANGED])) {
            throw new BadRequestHttpException("Приме завершен или находится в работе");
        }

        //только при записи по телефону, по мосру уведомления через ЕТП отправляются, для живой очереди уведомления не нужны
        if (!$visit->canNotify()) {
            throw new BadRequestHttpException("Для данного канала записи информирование не предусмотрено");
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($visit->owner);

        if (empty($contacts)) {
            throw new BadRequestHttpException("Не найдено контактов для информирования");
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new TransferVisitEvent([
            'visit' => $visit,
            'contacts' => $contacts,
            'id_visit' => $visit->id
        ]));

        return true;
    }

    /**
     * Получение неоплаченных приемов
     * @see https://jira.altarix.ru/browse/VETAIS-3241
     * @param int $id_owner
     * @param array $id_pet
     * @return array
     */
    public function unpaidVisits($id_owner = null, $id_pet = null)
    {
        $owner_visits = Visits::find()
            ->select('id, fact_start_dttm, id_organization')
            ->with(['organization' => function (ActiveQuery $q) {
                $q->select('id, name, short_name');
            }])
            ->with(['pets' => function (ActiveQuery $q) {
                $q
                    ->alias('p')
                    ->select([
                        'p.id',
                        'p.id_pet_tmp',
                        'p.name',
                        'p.id_species',
                        // Подменяем кличку Питомца для данных неавторизованного пользователя mosru
                        new Expression('CASE WHEN (tmpp.id IS NOT NULL) THEN tmpp.name ELSE p.name END AS "name"'),
                    ])
                    ->joinWith(['tmpPet' => function ($query) {
                        /** @var ActiveQuery $query */
                        $query->alias('tmpp');
                    }])
                    ->joinWith('species');
            }])
            ->where([
                'id_owner' => $id_owner,
                'status' => 'W',
                'is_paid' => false
            ])
            ->asArray()
            ->all();

        $pet_visits = Visits::find()
            ->select('visits.id, vp.id_pet, fact_start_dttm, id_organization')
            ->addSelect([
                new Expression('CASE WHEN (tmpp.id IS NOT NULL) THEN tmpp.name ELSE p.name END AS "pet_name"'),
            ])
            ->with(['organization' => function (ActiveQuery $q) {
                $q->select('id, name, short_name');
            }])
            ->leftJoin('visit_pets vp', 'vp.id_visit=visits.id')
            ->leftJoin('pets p', 'vp.id_pet = p.id')
            ->leftJoin('pets_tmp tmpp', 'p.id_pet_tmp = tmpp.id')
            ->where([
                'status' => 'W',
                'is_paid' => false
            ])
            ->andWhere(['vp.id_pet' => $id_pet])
            ->asArray()
            ->all();
        return [
            'owner_visits' => $owner_visits,
            'pet_visits' => $pet_visits
        ];
    }

    /**
     * Формирование запроса для получения списка визитов
     *
     * @param int $idOrganization
     * @param array $filter
     * @param array|int|null $idShiftType
     * @param Expression|null $expression
     * @return ActiveQuery
     * @throws BadRequestHttpException
     * @throws \yii\base\NotSupportedException
     */
    private function prepareQuery(int $idOrganization, array $filter, $idShiftType = null, Expression $expression = null): ActiveQuery
    {
        $query = Visits::find()
            ->select([
                'channel AS id_shift_type',
                'status',
                'change_reason',
                'cancel_initiator',
                'is_paid',
                'start_dttm',
                'ticket_number',
                'fact_start_dttm',
                'error_type',
                'fact_end_dttm',
                'st.description as channel',
                Visits::tableName() . '.duration',
                Visits::tableName() . '.cooldown',
                'visits.id',
                Visits::tableName() . '.id_owner',
                Visits::tableName() . '.type',
                Visits::tableName() . '.variety',
                Visits::tableName() . '.visit_to_address',
                Visits::tableName() . '.description',
                Visits::tableName() . '.created_at',
                'vs.id_specialist',
                Visits::tableName() . '.is_signed',
                "br.name AS brigade_name",
            ])
            ->where(['in', Visits::tableName() . '.type', [Visits::TYPE_VISIT, Visits::TYPE_AT_HOME, Visits::TYPE_ONLINE, Visits::TYPE_AMBULANCE]])
            ->andWhere([Visits::tableName() . '.id_organization' => $idOrganization,])
            ->leftJoin('brigades br', 'public.visits.id_brigade = br.id')
            ->leftJoin('shift_type st', 'public.visits.channel = st.id')
            ->joinWith('visitsSpecialists AS vs', false)
            ->with('visits_gov_services')
            ->with('specialists');

        $query->createCommand()->getRawSql();


        $fullTrimValidator = new FullTrimValidator();
        if ($idShiftType) {
            $query->andWhere(['channel' => $idShiftType,]);
        }

        if ($expression) {
            $query->andWhere($expression);
        }

        if (isset($filter['id_owner']) && is_numeric($filter['id_owner'])) {
            $query->andWhere([Visits::tableName() . '.id_owner' => $filter['id_owner']]);
        }

        if (!empty($filter['service_type_ids'])) {
            $query->leftJoin(VisitsGovServices::tableName() . ' AS vgs', 'public.visits.id = vgs.id_visit');
            $query->leftJoin(GovServices::tableName() . ' AS gserv', 'vgs.id_service = gserv.id');
            $query->andWhere(['in', 'gserv.id_service_type', $filter['service_type_ids']]);
        }

        if (isset($filter['ticket_number']) && !empty($filter['ticket_number'])) {
            $ticket_number = $fullTrimValidator->validateValue($filter['ticket_number']);
            $query->andWhere(['ilike', 'ticket_number', $ticket_number]);
        }

        if (isset($filter['change_reason']) && !empty($filter['change_reason'])) {
            if ($filter['change_reason'] == 'ABSENSE') {
                $query->andWhere([Visits::tableName() . '.change_reason' => 'ABSENSE']);
            } else {
                $query->andWhere(['IS NOT', Visits::tableName() . '.change_reason', null]);
            }
        }

        if (isset($filter['is_paid']) && \is_bool($filter['is_paid'])) {
            $query->andWhere([Visits::tableName() . '.is_paid' => $filter['is_paid']]);
        }

        if (isset($filter['status']) && \is_array($filter['status'])) {
            $status = [];
            foreach ($filter['status'] as $filterStatus) {
                /**
                 * Тип, чтоб если с буквой попал, но с регистром накосячил
                 */
                $filterStatus = strtoupper($filterStatus);
                if (!\in_array($filterStatus, VisitStatus::getStatusList(), true)) {
                    throw new BadRequestHttpException('Переданный параметр status не валиден');
                }
                $status[] = $filterStatus;
            }
            $assignedStatus = in_array(VisitStatus::NEW, $status) || in_array(VisitStatus::CHANGED, $status);
            $query->andWhere(['IN', Visits::tableName() . '.status', $status]);
        }

        if (isset($filter['status']) && is_string($filter['status'])) {
            $query->andWhere(['IN', Visits::tableName() . '.status', $filter['status']]);
        }

        if ((!isset($assignedStatus) || $assignedStatus) &&
            (!isset($idShiftType) || $this->checkShiftTypesContainsLiveQueue($idShiftType)) &&
            isset($filter['not_assigned']) &&
            $filter['not_assigned'] && isset($filter['id_specialist']) && is_numeric($filter['id_specialist'])) {

            $query->andWhere(['OR', ['vs.id_specialist' => $filter['id_specialist']], ['vs.id_specialist' => null]]);
        } elseif (isset($filter['id_specialist']) && is_numeric($filter['id_specialist']) && $idShiftType && $idShiftType[0] == 10) {
            $query->andWhere(['OR', ['vs.id_specialist' => $filter['id_specialist']], ['vs.id_specialist' => null]]);
        } elseif (isset($filter['id_specialist']) && is_numeric($filter['id_specialist'])) {
            $query->andWhere(['vs.id_specialist' => $filter['id_specialist']]);
        }

        if (isset($filter['type']) && in_array($filter['type'], [Visits::TYPE_VISIT, Visits::TYPE_AT_HOME, Visits::TYPE_ONLINE, Visits::TYPE_AMBULANCE], true)) {
            $query->andWhere([Visits::tableName() . '.type' => $filter['type']]);
        }
        if (isset($filter['is_signed']) && is_bool($filter['is_signed'])) {
            $query->andWhere([Visits::tableName() . '.is_signed' => $filter['is_signed']]);
        }
        $variety = $filter['variety'] ?? null;
        if (in_array($variety, Visits::varieties(), true)) {
            $query->andWhere([Visits::tableName() . '.variety' => $variety]);
        }

        return $query;
    }

    /**
     * подготовка параметров дат к использованию в запросе с time_range
     *
     * @param $strDateTimeFrom
     * @param $strDateTimeTo
     * @return Expression
     * @throws BadRequestHttpException
     */
    protected function prepareDateExpressionTimeRange($strDateTimeFrom, $strDateTimeTo): Expression
    {
        if ($strDateTimeFrom === false || $strDateTimeTo === false) {
            throw new BadRequestHttpException('Переданная дата невалидна');
        }

        return new Expression("(time_range && tsrange('{$strDateTimeFrom}', '{$strDateTimeTo}', '[)'))");
    }

    /**
     * подготовка параметров дат к использованию в запросе со start_dttm
     *
     * @param $strDateTimeFrom
     * @param $strDateTimeTo
     * @return Expression
     * @throws BadRequestHttpException
     */
    protected function prepareStartDateExpression($strDateTimeFrom, $strDateTimeTo): Expression
    {
        if ($strDateTimeFrom === false || $strDateTimeTo === false) {
            throw new BadRequestHttpException('Переданная дата невалидна');
        }

        return new Expression('(' . Visits::tableName() .
            ".created_at <@ tsrange('{$strDateTimeFrom}', '{$strDateTimeTo}', '[)'))");
    }

    /**
     * Преобразование даты старта и продолжительности к начальной и конечной дате
     *
     * @param $dateFrom
     * @param $daysCount
     * @return array
     * @throws \Exception
     */
    private function convertIntervalToDates($dateFrom, $daysCount): array
    {
        $dateTimeFrom = \DateTime::createFromFormat('Y-m-d', $dateFrom);
        $tempDate = clone $dateTimeFrom;
        $dateTimeTo = $tempDate->modify("+ {$daysCount} days");
        $now = new \DateTime();

        $strDateTimeFrom = $dateTimeFrom->format('Y-m-d 00:00:00');
        $strDateTimeTo = $dateTimeTo->format('Y-m-d 00:00:00');
        return [
            $strDateTimeFrom,
            $strDateTimeTo,
            $dateTimeFrom <= $now && $dateTimeTo >= $now
        ];
    }

    /**
     * Проверка на то, что переданный на вход id_shift_type является типом ЖО или содержит тип ЖО
     *
     * @param $idShiftType
     * @return bool
     */
    private function checkShiftTypesContainsLiveQueue($idShiftType): bool
    {
        $result = false;
        if (empty($idShiftType)) {
            $result = true;
        } else {
            $dbId = ShiftType::find()->select(['id'])->where(['type' => 'LIVE_QUEUE'])->one()->id;
            if (\is_array($idShiftType) && \in_array($dbId, $idShiftType, false)) {
                $result = true;
            } elseif (is_numeric($idShiftType) && $idShiftType === $dbId) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Запрос-обертка основного запроса для того, чтобы сделать сложный ORDER BY над UNION
     *
     * @param ActiveQuery $subQuery
     * @return array
     */
    private function executeSelectWithOrder(ActiveQuery $subQuery): array
    {
        $res = Visits::find()
            ->select(['*'])
            ->from(['sub' => $subQuery])
            ->orderBy([new Expression(
                'CASE
                    WHEN ((start_dttm IS NULL) AND (fact_start_dttm IS NULL))
                    THEN sub.created_at
                    WHEN ((start_dttm IS NULL) AND (fact_start_dttm IS NOT NULL))
                    THEN fact_start_dttm
                    ELSE start_dttm
                    END'),
            ])
            ->with(['visitsGovServices' => function ($visitsGovServicesQuery) {
                /* @var $visitsGovServicesQuery \yii\db\ActiveQuery */
                $visitsGovServicesQuery->select([
                    'id_visit',
                    'id_service',
                ])
                    ->joinWith('serviceTypes');
            }])
            ->with(['visitsGovServices' => function ($visitsGovServicesQuery) {
                /* @var $visitsGovServicesQuery \yii\db\ActiveQuery */
                $visitsGovServicesQuery->select([
                    'id_pet',
                    'id_visit',
                    'id_service',
                    'gov_services.name as service',
                    'service_types.name as type',
                ])
                    ->joinWith('serviceTypes')->orderBy('id_pet ASC',);
            }])
            ->with(['pets' => function ($petsQuery) {
                /** @var ActiveQuery $query */
                $petsQuery
                    ->alias('p')
                    ->select([
                        'p.*',
                        // Подменяем кличку Питомца для данных неавторизованного пользователя mosru
                        new Expression('CASE WHEN (tmpp.id IS NOT NULL) THEN tmpp.name ELSE p.name END AS "name"'),
                    ])
                    ->joinWith(['tmpPet' => function ($query) {
                        /** @var ActiveQuery $query */
                        $query->alias('tmpp');
                    }])
                    ->joinWith(['species' => function ($speciesQuery) {
                        /* @var $speciesQuery ActiveQuery */
                        $speciesQuery->select([
                            'id',
                            'name',
                        ]);
                    }]);
            }])
            ->with(['specialists' => function ($specialistQuery) {
                /* @var $specialistQuery \yii\db\ActiveQuery */
                $specialistQuery->joinWith('user', false)
                    ->select(array_merge(['specialists.*'], Specialists::personalAttributes()));
            }])
            ->with(['owner' => function ($query) {
                /** @var ActiveQuery $query */
                $query
                    ->alias('o')
                    ->select([
                        'o.*',
                        // Подменяем имя Владельца для данных неавторизованного пользователя mosru
                        new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.f_fio ELSE o.f_fio END AS "f_fio"'),
                        new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.i_fio ELSE o.i_fio END AS "i_fio"'),
                        new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.o_fio ELSE o.o_fio END AS "o_fio"'),
                        new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.fullname ELSE o.fullname END AS "fullname"'),
                    ])
                    ->joinWith(['tmpOwner' => function ($query) {
                        /** @var ActiveQuery $query */
                        $query->alias('tmpo');
                    }]);
            }])
            ->with(['ownerPhoneContacts' => function ($ownerPhoneContacts) {
                /** @var ActiveQuery $ownerPhoneContacts * */
                $ownerPhoneContacts
                    ->select(['*']);
            }])
            ->asArray()
            ->all();
        return $res;

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
    public function list2($page, $limit, $id_owner, $filter)
    {
//        $pet = Pets::findOne(['id' => $id_pet]);
//
//        if ($pet->is_main === true && !empty($pet->duplicates)) {
//            // На вкладке "Оказанные услуги"  основного животного отображается сводная информация по приемам,
//            // включая приемы животных-дублей
//            $duplicates_ids = ArrayHelper::getColumn($pet->duplicates, 'id');
//            array_unshift($duplicates_ids, $id_pet);
//            $id_pet = $duplicates_ids;
//        }
//
//        $this->validateFilter($filter);

        $query = Visits::find()
            ->select([
                'visits.*',
//                'public.visits_gov_services.id_pet AS pet_id_realy',
//                'public.pets.name AS pet_name',
//                'public.gov_services.id_service_type AS service_type',
//                'lower(time_range)::date',
            ])
//            ->joinWith([
//                'visitsGovServices' => function (ActiveQuery $query) use ($id_pet, $filter) {
//                    $query
//                        ->leftJoin('pets p', 'p.id = visits_gov_services.id_pet')
//                    ;
//                    return $query->select('visits_gov_services.id, id_visit, id_service, visits_gov_services.id_pet, p.name pet_name');
//                }
//            ])
//            ->joinWith([
//                'visitsGovServices.service' => function ($query) use ($filter) {
//                    /** @var ActiveQuery $query */
//                    $fullTrimValidator = new FullTrimValidator();
//
//                    if (!empty($filter['cod'])) {
//                        if (!is_array($filter['cod'])) {
//                            $filter['cod'] = [$filter['cod']];
//                        }
//                        $codes = [];
//                        foreach ($filter['cod'] as $code) {
//                            $codes[] = $fullTrimValidator->validateValue($code);
//                        }
//
//                        $query->andFilterWhere($this->prepareCodesCondition($codes));
//                    }
//
//                    if (!empty($filter['service_name'])) {
//                        $query->andWhere(['ILIKE', 'gov_services.name', $filter['service_name']]);
//                    }
//
//
//
//                    if (!empty($filter['id_service_type'])) {
//                        $query->andWhere(['gov_services.id_service_type' => $filter['id_service_type']]);
//                    }
//                    return $query;
//                }
//            ])
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
            ->where(['=', 'id_owner', $id_owner]);
//            ->andWhere(['!=', 'visits.status', "A"]);

//        return $query->asArray()->all();
        $records = $query->asArray()->all();
//         $result = new Lists(
//            $records,
//            $query
//                ->limit(100)
//                ->offset(null)
//                ->count(count($records))
//        );
////
//        $result->customPagination($page, $limit);

        return $records;

//            ->joinWith([
//                'specialists' => function ($specialistQuery) {
//                    /* @var $specialistQuery \yii\db\ActiveQuery */
//                    $specialistQuery->joinWith('user', false)
//                        ->select('specialists.id, users.fullname');
//                }
//            ])
//            // ->andWhere(['public.visits.id_pet' => $id_pet])
//
//            ->leftJoin("public.pets", "public.visits_gov_services.id_pet=public.pets.id")
//            ->orderBy(['public.visits.id' => SORT_DESC]);
//
//        //переработана логика выдачи услуг по запросу в связи с интеграцией с суперсервисом. Предыдущая логика не включала новые приемы, в статусе N. Переработанная логика включаеткак эти статусы, так и приемы, сохраненные по входящему Soap запросу от МПГУ. Старая логика в файле PetServiceModel_old
//        if (!empty($filter['is_refusal_to_vaccinate2'])) {
//            if ($filter['is_refusal_to_vaccinate2'] === true) {
//                $query
//                    ->andWhere(['visits_gov_services.id_pet' => $id_pet])
//                    ->andWhere(['!=', 'visits.channel', 5]);
//            }
//        } else {
//            $query
//                ->andWhere(['or', ['visits_gov_services.id_pet' => $id_pet]])
//                ->orWhere(['and', ['public.visits.id_pet' => $id_pet]]);
//        }
//
//        $query->andWhere(['in', 'status', [VisitStatus::NEW , VisitStatus::IN_WORK, VisitStatus::FINISHED, VisitStatus::CANCELED, VisitStatus::TRANSFER,]]);
//
//
//        // Применяем фильтры
//        if (!empty($filter)) {
//            $query = $this->applyFilter($filter, $query);
//        }
//
//        $records = $query
//            ->asArray()
//            ->all();
//
//        // VETAIS-2159
//        // костыль для заполнения reports для "виртуальных" отчетов (не имеющих печатной формы в reports)
//        foreach ($records as &$record) {
//            foreach ($record['visitsGovServices'] as &$visitsGovService) {
//                if (empty($visitsGovService['service']) || !is_array($visitsGovService['service'])) {
//                    continue;
//                }
//                if (empty($visitsGovService['service']['reports']) && !empty($visitsGovService['service']['outParams'])) {
//                    $visitsGovService['service']['reports'] = [
//                        [
//                            'id' => -1,
//                            'name' => 'VIRTUAL',
//
//                        ],
//                    ];
//                }
//            }
//        }
//
//        // Формируем ответ
//        $result = new Lists(
//            $records,
//            $query
//                ->limit(NULL)
//                ->offset(null)
//                ->count('distinct "visits"."id"')
//        );
//
//        $result->customPagination($page, $limit);
//
//        //проверяем на наличие и добавляем ссылки guid на файлы из ЦХЭД
//
//
//        foreach ($result->visits as &$item) {
//            $rows = (new \yii\db\Query())
//                ->select(['hash'])
//                ->from('files')
//                ->where([
//                    'entity_id' => $item['id'],
//                    'entity_type' => 'visits-mos-ru',
//                ])
//                ->all();
//
//            if (count($rows) > 0) {
//                $item["guid_file_superservice"] = $rows[0]['hash'];
//            }
//            ;
//
//            if ($item['pet_name'] == null) {
//                if ($item['id_pet'] != null) {
//                    $pet_name_from_table = (new \yii\db\Query())
//                        ->select(['name'])
//                        ->from('pets')
//                        ->where([
//                            'id' => $item['id_pet']
//                        ])
//                        ->all();
//                    $item['pet_name'] = $pet_name_from_table[0]['name'];
//                } elseif ($item['pet_id_realy'] != null) {
//                    $pet_name_from_table = (new \yii\db\Query())
//                        ->select(['name'])
//                        ->from('pets')
//                        ->where([
//                            'id' => $item['id_pet']
//                        ])
//                        ->all();
//                    $item['pet_name'] = $pet_name_from_table[0]['name'];
//                }
//            }
//        }
//        return
//            $result;
    }

}
