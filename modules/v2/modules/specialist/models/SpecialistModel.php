<?php

namespace app\modules\v2\modules\specialist\models;

use app\common\components\rbac\Role;
use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\models\db\GovServices;
use app\models\db\Shifts;
use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\models\db\Visits;
use app\modules\v2\modules\emergency\models\EmergencyModel;
use app\modules\v2\modules\specialist\skeletons\specialist\Lists;
use app\modules\v2\modules\specialist\skeletons\specialist\Specialist;
use app\modules\v2\modules\specialist\skeletons\specialist\SpecialistList;
use app\modules\v2\modules\visit\services\VisitDurationsService;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\helpers\ArrayHelper;

class SpecialistModel
{
    private $specialistTable;
    private $shiftTable;
    private $shiftTypeTable;
    private $timeSheetsTable;
    private $startToday;
    private $endToday;
    private $visitsTable;

    public const
        TIMESLOT_FREE = 'FREE',
        TIMESLOT_UNAVAILABLE = 'UNAVAILABLE',
        TIMESLOT_VISIT = 'VISIT',
        TIMESLOT_INSUFFICIENT_DURATION = 'INSUFFICIENT_DURATION'
    ;

    /**
     * SpecialistModel constructor.
     */
    public function __construct()
    {
        $dateTimeNow = new \DateTime();
        $this->startToday = $dateTimeNow->format('Y-m-d 00:00:00');
        $this->endToday = $dateTimeNow->format('Y-m-d 23:59:59');
        $this->specialistTable = Specialists::tableName();
        $this->shiftTable = Shifts::tableName();
        $this->shiftTypeTable = ShiftType::tableName();
        $this->timeSheetsTable = Timesheets::tableName();
        $this->visitsTable = Visits::tableName();
    }

    /**
     * Метод выбора специалиста или специализации для записи в ЖО
     *
     * @param int $idOrganization
     * @param int $idShiftType
     * @param int $page
     * @param int $limit
     * @return Lists
     * @throws BadRequestHttpException
     */
    public function getTimeLiveQueneList(int $idOrganization, int $idShiftType, int $page = 1, int $limit = 10): Lists
    {
        if (ShiftType::find()->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE])->one()->id !== $idShiftType) {
            throw new BadRequestHttpException('id_shift_type имеет тип, отличный от ЖО');
        }

        $specialistsQuery = $this->prepareSpecialistsQuery($idOrganization, $idShiftType);
        $specialists = $this->getSpecialistsWithTimeLiveQueue($specialistsQuery, $page, $limit);
        $times = $this->getTimesWithTimeLiveQueue($specialists);

        $result = new Lists(
            $specialists->all(),
            $times,
            $specialists->count()
        );
        $result->customPagination($page, $limit);

        return $result;
    }

    /**
     * Подготовленный запрос со специалистами
     *
     * @param $idOrganization
     * @param $idShiftType
     * @return ActiveQuery
     */
    private function prepareSpecialistsQuery($idOrganization, $idShiftType): ActiveQuery
    {
        $dateExpression = new Expression("(date && tsrange('{$this->startToday}', '{$this->endToday}', '[)'))");

        $query = Specialists::find()
            ->select('specialists.*')
            ->addSelect(Specialists::personalAttributes())
            ->joinWith('user', false)
            ->leftJoin($this->timeSheetsTable, "{$this->timeSheetsTable}.id_specialist = {$this->specialistTable}.id")
            ->leftJoin($this->shiftTable, "{$this->shiftTable}.id = {$this->timeSheetsTable}.id_shift")
            ->leftJoin($this->shiftTypeTable, "{$this->shiftTable}.id_type = {$this->shiftTypeTable}.id")
            ->andWhere(["{$this->specialistTable}.id_organization" => $idOrganization])
            ->andWhere(["{$this->shiftTypeTable}.id" => $idShiftType])
            ->andWhere(['OR',
                ["{$this->specialistTable}.expel_date" => NULL],
                ['>', "{$this->specialistTable}.expel_date", new Expression('NOW()')]
            ])
            ->andWhere($dateExpression)
            ->orderBy("{$this->specialistTable}.id");

        return $query;
    }

    /**
     * Ограничение запроса специалистов по параметрам пагинации
     *
     * @param \yii\db\ActiveQuery $specialistsQuery
     * @param $page
     * @param $limit
     * @return \yii\db\ActiveQuery
     */
    private function getSpecialistsWithTimeLiveQueue($specialistsQuery, $page, $limit)
    {
        $specialistsQuery
            ->limit($limit)
            ->offset($limit * ($page - 1));

        return $specialistsQuery;
    }

    /**
     * Получение таймшитов определенных специалистов за день
     *
     * @param \yii\db\ActiveQuery $specialists
     * @return ShiftType[]|Specialists[]|Timesheets[]|array|\yii\db\ActiveRecord[]
     */
    private function getTimesWithTimeLiveQueue($specialists): array
    {
        $dateExpression = new Expression(
            "({$this->timeSheetsTable}.date && tsrange('{$this->startToday}', '{$this->endToday}', '[)'))"
        );

        $specialistsQuery = clone $specialists;

        $query = Timesheets::find()
            ->select([
                "{$this->timeSheetsTable}.id",
                "{$this->timeSheetsTable}.id_specialist",
                "{$this->timeSheetsTable}.id_shift",
                new Expression('lower(date) AS from'),
                new Expression('upper(date) AS to'),
                new Expression('FALSE AS manual_shift'),
                new Expression('FALSE AS shortened_day')
            ])
            ->joinWith(['shifts_type' => function ($shiftTypeQuery) {
                $shiftTypeQuery->select([
                    '*'
                ]);
            }])
            ->andWhere(['IN', "{$this->shiftTypeTable}.id", ShiftType::getTypesIdWithTimeLiveQueue()])
            ->andWhere(['IN', "{$this->timeSheetsTable}.id_specialist", $specialistsQuery->select(["{$this->specialistTable}.id"])])
            ->andWhere($dateExpression)
            ->asArray()
            ->all();
        return $query;
    }

    /**
     * Метод выбора таймслотов специалиста
     *
     * @param int    $idOrganization
     * @param string $dateFrom
     * @param int    $idSpecialist
     * @param int    $idShiftType
     * @param array  $services
     * @param string $variety   разновидность приёма (MULTIPLE | BROOD | SINGLE)
     * @param int    $countPets кол-во животных в приеме
     * @param string $type      тип приема (VISIT | AT_HOME)
     *
     * @return Specialist
     * @throws BadRequestHttpException
     */
    public function getTimeList(
        int $idOrganization,
        string $dateFrom,
        int $idSpecialist,
        int $idShiftType,
        array $services,
        string $variety,
        int $countPets = 1,
        string $type = Visits::TYPE_VISIT
    ): Specialist
    {
        $date = \DateTime::createFromFormat('Y-m-d', $dateFrom);
        if ($date === false) {
            throw new BadRequestHttpException('date_from имеет недопустимый формат');
        }

        $shiftTypes = ShiftType::find()
            ->select('id, type')
            ->where(['IN', 'type', [
                ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY,
                ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT,
                ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT,
                ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE
            ]])
            ->asArray()
            ->all();

        $shiftTypes = ArrayHelper::index($shiftTypes, 'id');
        if (array_key_exists($idShiftType, $shiftTypes) === false) {
            throw new BadRequestHttpException('id_shift_type имеет недопустимый тип');
        }

        /* @var $specialist \app\models\db\Specialists */
        $specialist = Specialists::find()
            ->select(['specialists.id', 'id_user'])
            ->addSelect(Specialists::personalAttributes())
            ->joinWith('user', false)
            ->where(['specialists.id' => $idSpecialist])
            ->andWhere(['id_organization' => $idOrganization])
            ->one();
        if (!$specialist) {
            throw new BadRequestHttpException('Специалист не найден');
        }

        if ($specialist->isExpelledAtDate($dateFrom)) {
            // специалист уже уволен на текущую дату, дальше ничего не ищем и не проверяем
            // возвращаем пустой массив вместо слотов
            return new Specialist(
                $specialist->toArray(),
                []
            );
        }

        $dateStart = $date->format('Y-m-d 00:00:00');
        $dateEnd = $date->format('Y-m-d 23:59:59');
        $timesheetsDateExpression = new Expression(
            "({$this->timeSheetsTable}.date && tsrange('{$dateStart}', '{$dateEnd}', '[)'))"
        );

        $workdayTimeSheet = $this->prepareTimesheetsQuery(
                $idSpecialist,
                ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY,
                $timesheetsDateExpression)
            ->asArray()
            ->one();

        if (!$workdayTimeSheet) {
            throw new BadRequestHttpException('WORKDAY timesheet специалиста не найден');
        }

        $flagCallToHome = ($type == Visits::TYPE_AT_HOME);

        /*
        * Быстрая правка. ранее что бы записаться на дом необходимо было пересечение записи по телефону и вызова на дом
        * Сейчас такое невозможно, перебиваем на поиск просто записи домой
        */
        if ($flagCallToHome && $shiftTypes[$idShiftType]['type'] === ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT) {
            foreach ($shiftTypes as $fix_type){
                if ($fix_type['type'] == ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY){
                    $idShiftType = $fix_type['id'];
                }
            }
        }



        $idleTimeSheets = $this->getChildTimesheetsIdle($idSpecialist, $workdayTimeSheet['id']);

        $subTimeSheets = [];
        if ($shiftTypes[$idShiftType]['type'] === ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT || $shiftTypes[$idShiftType]['type'] === ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT) {

            $subTimeSheets = $this->getChildTimesheetsWithShiftType($idSpecialist, $workdayTimeSheet['id'], $idShiftType);

            if (empty($subTimeSheets)) {
                if ($shiftTypes[$idShiftType]['type'] === ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT) {
                    throw new BadRequestHttpException('В указанный день у врача нет записи по телефону');
                }
                elseif ($shiftTypes[$idShiftType]['type'] === ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT) {
                    throw new BadRequestHttpException('В указанный день у врача нет записи c mos.ru');
                }
            }

            if ($flagCallToHome) {

                $subTimeSheetsCallHome = $this->prepareTimesheetsQuery(
                    $idSpecialist,
                    ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME,
                    $timesheetsDateExpression)
                    ->andWhere(["{$this->timeSheetsTable}.parent_id" => $workdayTimeSheet['id']])
                ;

                $subWhere = [];
                foreach ($subTimeSheets AS $subTimeSheet) {
                    $subWhere[] = new Expression("(date && tsrange('{$subTimeSheet['date_lower']}', '{$subTimeSheet['date_upper']}', '[)'))");
                }
                if (\count($subWhere) === 1) {
                    $where = ['AND',
                        $subWhere[0]
                    ];
                }
                else {
                    $where = [];
                    $where[] = 'OR';
                    foreach ($subWhere AS $sub) {
                        $where[] = $sub;
                    }
                }
                $subTimeSheetsCallHome = $subTimeSheetsCallHome
                    ->andWhere($where)
                    ->asArray()
                    ->one()
                ;

                if (!$subTimeSheetsCallHome) {
                    $caseTypeCallMessage = $shiftTypes[$idShiftType]['type'] === ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT ?
                        'по телефону' :
                        'c mos.ru'
                    ;
                    throw new BadRequestHttpException("В указанный день у врача нет записи {$caseTypeCallMessage} с вызовом на дом");
                }
                $subTimeSheetCallHomeLower = new \DateTime($subTimeSheetsCallHome['date_lower']);
                $subTimeSheetCallHomeUpper = new \DateTime($subTimeSheetsCallHome['date_upper']);
            }
        }

        if ($flagCallToHome && $shiftTypes[$idShiftType]['type'] === ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY) {
            $workDayTimeSheetCallHome = $this->prepareTimesheetsQuery(
                    $idSpecialist,
                    ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME,
                    $timesheetsDateExpression)
                ->andWhere(["{$this->timeSheetsTable}.parent_id" => $workdayTimeSheet['id']])
                ->asArray()
                ->one();
            if (!$workDayTimeSheetCallHome) {
                throw new BadRequestHttpException('WORKDAY timesheet с вызовом на дом специалиста не найден');
            }

            $workDayTimeSheetCallHomeLower = new \DateTime($workDayTimeSheetCallHome['date_lower']);
            $workDayTimeSheetCallHomeUpper = new \DateTime($workDayTimeSheetCallHome['date_upper']);
        }

        $dateLower = new \DateTime($workdayTimeSheet['date_lower']);
        $dateUpper = new \DateTime($workdayTimeSheet['date_upper']);

        $period = new \DatePeriod(
            $dateLower,
            new \DateInterval('PT10M'),
            $dateUpper
        );
        $slots = [];

        if ($type == Visits::TYPE_VISIT) {
            $emergencies = (new EmergencyModel())->getAllOrganizationsEmergencyForPeriod($idOrganization, $dateLower->format('Y-m-d H:i:s'), $dateUpper->format('Y-m-d H:i:s'));
            $emergencyFlag = \count($emergencies) > 0 ;
        } else {
            $emergencyFlag = false;
        }

        // находим существующие приемы, чтобы пометить слоты занятыми
        $visits = $this->getVisits($idSpecialist, new Expression("({$this->visitsTable}.time_range && '{$workdayTimeSheet['date']}'::tsrange)"));

        foreach ($period as $slot) {
            $status = self::TIMESLOT_UNAVAILABLE;
            if ($slot->getTimestamp() < time() || $specialist->isExpelledAtDate($slot->format('Y-m-d'))) {
                $status = self::TIMESLOT_UNAVAILABLE;
            }
            else {
                foreach ($subTimeSheets as $timesheet) {
                    $timeSheetDateLower = new \DateTime($timesheet['date_lower']);
                    $timeSheetDateUpper = new \DateTime($timesheet['date_upper']);
                    if ($flagCallToHome) {
                        $timeSheetDateLower = $timeSheetDateLower > $subTimeSheetCallHomeLower ?
                            $timeSheetDateLower : $subTimeSheetCallHomeLower;
                        $timeSheetDateUpper = $timeSheetDateUpper < $subTimeSheetCallHomeUpper ?
                            $timeSheetDateUpper : $subTimeSheetCallHomeUpper;
                    }
                    if ($slot >= $timeSheetDateLower && $slot < $timeSheetDateUpper) {
                        $status = self::TIMESLOT_FREE;
                        break;
                    }
                }
                if ($shiftTypes[$idShiftType]['type'] !== ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT
                    && $shiftTypes[$idShiftType]['type'] !== ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT) {
                    if ($flagCallToHome) {
                        if ($slot >= $workDayTimeSheetCallHomeLower && $slot < $workDayTimeSheetCallHomeUpper) {
                            $status = self::TIMESLOT_FREE;
                        }
                        else {
                            $status = self::TIMESLOT_UNAVAILABLE;
                        }
                    }
                    else {
                        $status = self::TIMESLOT_FREE;
                    }
                }

                if ($emergencyFlag) {
                    foreach ($emergencies as $emergency) {
                        $emergencyDateLower = new \DateTime($emergency->date_from);
                        $emergencyDateUpper = new \DateTime($emergency->date_to);
                        if ($slot >= $emergencyDateLower && $slot < $emergencyDateUpper) {
                            $status = self::TIMESLOT_UNAVAILABLE;
                            break;
                        }
                    }
                }

                foreach ($idleTimeSheets as $idle) {
                    if ($slot >= new \DateTime($idle['date_lower']) && $slot < new \DateTime($idle['date_upper'])) {
                        $status = self::TIMESLOT_UNAVAILABLE;
                        break;
                    }
                }
            }
            // для существующих приёмов помечаем слоты занятыми
            foreach ($visits as $visit) {
                if ($slot >= new \DateTime($visit['time_lower']) && $slot < new \DateTime($visit['time_upper'])) {
                    $status = self::TIMESLOT_VISIT;
                    break;
                }
            }
            $slots[] = [
                'time' => $slot->format('H:i'),
                'date' => $slot->format('Y-m-d'),
                'status' => $status
            ];
        }

        $slots = $this->markShortIntervals($slots, $services, $variety, $countPets);   // Вычеркиваем недоступные по продолжительности

        $result = new Specialist(
            $specialist->toArray(),
            $slots
        );

        return $result;

    }

    /**
     * @param array|null $filter
     * @param int $page
     * @param int $limit
     * @return SpecialistList
     * @throws \yii\base\NotSupportedException
     */
    public function getList(int $page, int $limit, array $filter = null): SpecialistList
    {
        $specialists = Specialists::find()
            ->select([
                'specialists.id',
                'id_organization',
                'specialists.id_user'
            ])
            ->addSelect(Specialists::personalAttributes())
            ->joinWith('user', false)
            ->with(['organization' => function ($query) {
                $query
                    ->select([
                        'id',
                        'name',
                        'short_name',
                    ]);
            }])
            ->limit($limit)
            ->offset($limit * ($page - 1))
            ->orderBy('fullname');

        $fullTrimValidator = new FullTrimValidator();

        if (isset($filter['id_organization'])) {
            $specialists
                ->where(['id_organization' => $filter['id_organization']]);
        }
        if (isset($filter['fio'])) {
            $fio = $fullTrimValidator->validateValue($filter['fio']);
            $specialists
                ->andWhere(['ilike', 'fullname', $fio]);
        }
        if (isset($filter['is_fired'])) {
            $specialists
                ->andWhere(
                    $filter['is_fired'] ?
                        ['not', ['expel_date' => null]] :
                        ['expel_date' => null]);
        }
        if (isset($filter['id_user'])) {
            $specialists
                ->andWhere(['id_user' => $filter['id_user']]);
        }
        if (isset($filter['ids'])) {
            $specialists
                ->andWhere(['in', 'specialists.id', $filter['ids']]);
        }

        if (isset($filter['specialist_only']) && $filter['specialist_only'] == true) {
            $specialists->innerJoin(
                'public.auth_assignment',
                'auth_assignment.id_specialist = public.specialists.id AND (
                    auth_assignment.item_name = :vetSpecGos OR auth_assignment.item_name = :vetSpecGosAmb)',
                [
                    ':vetSpecGos' => Role::ROLE_VET_SPECIALIST_GOS,
                    ':vetSpecGosAmb' => Role::ROLE_VET_SPECIALIST_GOS_AMB,
                ]
            )
                ->distinct(true); // в auth_assignment нет уникальности - может задублироваться
            ;
        }

        $specialistsArray = $specialists
            ->asArray()
            ->all();

        $result = new SpecialistList(
            $specialistsArray,
            $specialists->count()
        );
        $result->customPagination($page, $limit);
        return $result;
    }

    /**
     * @param string $fFio
     * @param string $iFio
     * @param string $oFio
     * @param string $sex
     * @param string $birthday
     * @param string $expelDate
     * @param int $idOrganization
     * @param int|null $photo
     * @return array
     * @throws BadRequestHttpException
     */
    public function create(string $fFio, string $iFio, ?string $oFio, string $sex, ?string $birthday, ?string $expelDate,
                           int $idOrganization, int $photo = null): array
    {
        $specialist = new Specialists([
            'f_fio' => $fFio,
            'i_fio' => $iFio,
            'o_fio' => $oFio,
            'sex' => $sex,
            'birthday' => $birthday,
            'expel_date' => $expelDate,
            'id_organization' => $idOrganization,
            'photo' => $photo,
            'reg_date' => (new \DateTime())->format('Y-m-d'),
        ]);

        return $this->saveSpecialist($specialist);
    }

    /**
     * @param int $id
     * @param string $fFio
     * @param string $iFio
     * @param string $oFio
     * @param string $sex
     * @param string $birthday
     * @param string $expelDate
     * @param int $idOrganization
     * @param int|null $photo
     * @return array
     * @throws BadRequestHttpException
     */
    public function update(int $id, string $fFio, string $iFio, ?string $oFio, string $sex, ?string $birthday, ?string $expelDate,
                           int $idOrganization, int $photo = null): array
    {
        $specialist = $this->getSpecialistById($id);

        if ($specialist->user === null) {
            throw new BadRequestHttpException('Учетная запись не привязана к специалисту, редактирование невозможно');
        }

        $user = $specialist->user;

        $personalAttributes = [
            'f_fio' => $fFio,
            'i_fio' => $iFio,
            'o_fio' => $oFio,
            'sex' => $sex,
            'birthday' => $birthday,
            'photo' => $photo,
        ];

        $specialistAttributes = [
            'expel_date' => $expelDate,
            'id_organization' => $idOrganization,
            'reg_date' => (new \DateTime())->format('Y-m-d'),
        ];

        if (!$this->validateOrganization($specialist, $idOrganization)) {
            $errors = $specialist->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении специалиста' : implode("\n", array_values($errors)));
        }

        if ($user->load($personalAttributes, '')) {
            if (!$user->save(true, array_keys($personalAttributes))) {
                $errors = $user->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении специалиста' : implode("\n", array_values($errors)));
            }
        }

        $specialist->load($specialistAttributes, '');

        return $this->saveSpecialist($specialist);
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     */
    public function delete(int $id): array
    {
        $specialist = $this->getSpecialistById($id);

        try {
            $specialist->delete();
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('Ошибка при удалении специалиста');
        }

        return ['result' => true];
    }

    /**
     * @param int $id
     * @return \app\models\db\Specialists
     * @throws \yii\web\BadRequestHttpException
     */
    private function getSpecialistById($id): Specialists
    {
        $specialist = Specialists::findOne($id);
        if ($specialist === null) {
            throw new BadRequestHttpException('Специалист не найден');
        }

        return $specialist;
    }

    /**
     * @param Specialists $specialist
     * @return array
     * @throws BadRequestHttpException
     */
    private function saveSpecialist(Specialists $specialist): array
    {
        $this->validateSpecialist($specialist);

        if (!$specialist->save(false)) {
            throw new BadRequestHttpException('Ошибка при сохранении специалиста');
        }

        return ['result' => true, 'id' => $specialist->id];
    }

    /**
     * @param Specialists $specialist
     * @throws BadRequestHttpException
     */
    private function validateSpecialist(Specialists $specialist): void
    {
        if ($specialist->validate() === false) {
            $errors = $specialist->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ?
                'Ошибка валидации' :
                implode("\n", array_values($errors)));
        }
    }

    /**
     * По списку сервсисов отмечаем слоты, на которые не может приходиться начало приема
     * Например,
     *      если пользователю доступно для записи 3 слота 10:00, 10:10, 10:20
     *      а требуемое время для проведения услуг составляет 20 минут
     *      последний слот (10:20) должен быть помечен INSUFFICIENT_DURATION,
     *      тк установив начало прима на него время приема будет состовлять всего 10 минут
     *
     *
     * @param array  $slots
     * @param array  $services
     * @param string $variety
     * @param int    $countPets
     *
     * @return array
     * @throws BadRequestHttpException
     */
    protected function markShortIntervals(array $slots, array $services, string $variety, int $countPets): array
    {
        $countSlots = $this->calcCountSlotsForServices($services, $variety, $countPets);

        if (empty($countSlots)){ // Тут нечего делать - возвращаем
            return $slots;
        }

        $reversedSlots = array_reverse($slots);

        $excludedCount = 0;
        foreach ($reversedSlots as &$slot){

            if ($slot['status'] !== self::TIMESLOT_FREE){
                $excludedCount = 0; // Интервал котоый мы могли вычеркивать - уже закончился, обнуляем
                continue;
            }

            /**
             * Примечение: для записи на 2 слота,
             * мы должны пометить только один последний как недоступный
             */
            if ($excludedCount > ($countSlots - 2)){
                continue;
            }

            /**
             * Начинаем вычеркивать слоты, которых не хватит под указанные услуги
             */
            $slot['status'] = self::TIMESLOT_INSUFFICIENT_DURATION;
            $excludedCount++;
        }
        unset($slot);

        return array_reverse($reversedSlots);
    }

    /**
     * Высчитывает необходимое кол-во слотов для указанных услуг
     *
     * @param array  $services
     * @param string $variety
     * @param int    $countPets
     *
     * @return int
     * @throws BadRequestHttpException
     */
    protected function calcCountSlotsForServices(array $services, string $variety, int $countPets): int
    {
        if (empty($services)){
            return 0;
        }

        $ids = $this->validateServices($services);

        $govServices = GovServices::find()
            ->select([
                'id',
                'duration',
                'for_broods',
                'for_multiple',
                new Expression('coalesce(cooldown,0) AS cooldown')
            ])
            ->where(['IN', 'id', $ids])
            ->all();
        $govServices = ArrayHelper::index($govServices,'id');
        [$duration, $cooldown] = VisitDurationsService::calculated($variety, $services, $govServices, $countPets);
        $duration += $cooldown;

        // Необходимое кол-во слотов
        return ceil((int) $duration/10);
    }

    /**
     * @todo Этот метод - это дубликат DatelistModel::validateServices,
     * но отличатеся возращаемым значением и форматом ошибок
     *
     * @param $services
     * @return array
     * @throws BadRequestHttpException
     */
    protected function validateServices($services): array
    {
        if (!\is_array($services)) {
            throw new BadRequestHttpException('Передан некорректный параметр services');
        }
        $ids = array_unique(ArrayHelper::getColumn($services, 'id'));
        $services = GovServices::find()
            ->select('id')
            ->where(['IN', 'id', $ids])
            ->asArray()
            ->all();
        // проверка на существование всех услуг
        if (\count($services) !== \count($ids)) {
            throw new BadRequestHttpException('Параметр services содержит несуществующие услуги');
        }

        return $ids;
    }

    /**
     * Подготавливает запрос на выборку таймшитов
     *
     * @param int         $idSpecialist
     * @param string      $shiftType
     * @param Expression  $timesheetsDateExpression
     *
     * @return ActiveQuery
     */
    private function prepareTimesheetsQuery($idSpecialist, $shiftType, $timesheetsDateExpression): ActiveQuery
    {
        return Timesheets::find()
            ->select(
                new Expression("{$this->timeSheetsTable}.*,
                lower(timesheets.date) AS date_lower,
                upper(timesheets.date) AS date_upper")
            )
            ->leftJoin($this->shiftTable, "{$this->shiftTable}.id = {$this->timeSheetsTable}.id_shift")
            ->leftJoin($this->shiftTypeTable, "{$this->shiftTable}.id_type = {$this->shiftTypeTable}.id")
            ->where([
                'AND',
                ["{$this->timeSheetsTable}.id_specialist" => $idSpecialist],
                ["{$this->shiftTypeTable}.type" => $shiftType],
                $timesheetsDateExpression
            ]);
    }

    /**
     * Возвращает дочерние таймшиты с shift_type.idle = true
     *
     * @param int $idSpecialist
     * @param int $idParent
     * @return array
     */
    private function getChildTimesheetsIdle($idSpecialist, $idParent): array
    {
        $query = $this->queryChildTimesheets($idSpecialist, $idParent);

        return $query
            ->leftJoin($this->shiftTypeTable, "{$this->shiftTable}.id_type = {$this->shiftTypeTable}.id")
            ->andWhere([
                "{$this->shiftTypeTable}.idle" => true
            ])
            ->asArray()
            ->all();
    }

    /**
     * Возвращает дочерние таймшиты с указанным shift_type
     *
     * @param int $idSpecialist
     * @param int $idParent
     * @param int $idShiftType
     * @return array
     */
    private function getChildTimesheetsWithShiftType($idSpecialist, $idParent, $idShiftType): array
    {
        $query = $this->queryChildTimesheets($idSpecialist, $idParent);

        return $query
            ->andWhere([
                "{$this->shiftTable}.id_type" => $idShiftType
            ])
            ->asArray()
            ->all();
    }

    /**
     * Возвращает запрос на выборку дочерних таймшитов
     *
     * @param int $idSpecialist
     * @param int $idParent
     * @return ActiveQuery
     */
    private function queryChildTimesheets($idSpecialist, $idParent): ActiveQuery
    {
        return Timesheets::find()
            ->select(
                new Expression("{$this->timeSheetsTable}.*,
                 lower(timesheets.date) AS date_lower, 
                 upper(timesheets.date) AS date_upper")
            )
            ->leftJoin($this->shiftTable, "{$this->shiftTable}.id = {$this->timeSheetsTable}.id_shift")
            ->where([
                'AND',
                ["{$this->timeSheetsTable}.parent_id" => $idParent],
                ["{$this->timeSheetsTable}.id_specialist" => $idSpecialist],
            ])
        ;
    }

    /**
     * Находим существующие приемы за период, чтобы пометить слоты занятыми,
     * при этом не учитываем приемы ЖО, т.к. они не должны препятствовать записи по телефону,
     * не учитываем при этом приемы НВП (https://jira.altarix.ru/browse/VETAIS-1900)
     *
     * @param int         $idSpecialist
     * @param Expression  $dateExpression
     * @return array
     */
    private function getVisits($idSpecialist, $dateExpression): array
    {
        return Visits::find()
            ->select(
                new Expression("{$this->visitsTable}.*,
                lower(time_range) AS time_lower,
                upper(time_range) AS time_upper")
            )
            ->leftJoin('visits_specialists', "visits_specialists.id_visit = {$this->visitsTable}.id")
            ->where([
                'AND',
                ['visits_specialists.id_specialist' => $idSpecialist],
                ['NOT IN', 'status', [VisitStatus::CANCELED, VisitStatus::TRANSFER]],
                ['!=', 'channel', ShiftType::getTypesIdWithTimeLiveQueue()],
                ['!=', 'type', Visits::TYPE_AMBULANCE],
                $dateExpression
            ])
            ->asArray()
            ->all();
    }

    /**
     * @param \app\models\db\Specialists $specialist
     * @param int                        $idOrganization
     * @return bool
     */
    private function validateOrganization(&$specialist, $idOrganization)
    {
        $attribute = 'id_organization';

        if (empty($idOrganization)) {
            $specialist->addError($attribute, 'Специалисту должна быть назначена организация');
            return false;
        }
        if (empty($specialist->id_organization) || $specialist->id_organization == $idOrganization) {
            return true;
        }

        $visitsExist = (new Query())
            ->select('vs.*')
            ->addSelect('v.id_organization')
            ->from('visits_specialists vs')
            ->leftJoin('visits v', '[[v]].[[id]] = [[vs]].[[id_visit]]')
            ->where([
                'id_specialist' => $specialist->id,
                'id_organization' => $specialist->id_organization,
            ])
            ->exists();
        if ($visitsExist === true) {
            $specialist->addError($attribute, 'Невозможно изменить организацию: у пользователя уже есть приемы в организации "' . $specialist->organization->short_name . '"');
            return false;
        }

        $timesheetsExist = (new Query())
            ->select('ts.*')
            ->addSelect('sh.id_organization')
            ->from('timesheets ts')
            ->leftJoin('shifts sh', '[[sh]].[[id]] = [[ts]].[[id_shift]]')
            ->where([
                'id_specialist' => $specialist->id,
                'id_organization' => $specialist->id_organization,
            ])
            ->exists();
        if ($timesheetsExist === true) {
            $specialist->addError($attribute, 'Невозможно изменить организацию: у пользователя уже есть расписания в организации "' . $specialist->organization->short_name . '"');
            return false;
        }

        $specialists = Specialists::find()
            ->where([
                'id_organization' => $idOrganization,
                'id_user' => $specialist->id_user,
                'expel_date' => null,
            ])
            ->all();

        if (!empty($specialists)) {
            $specialist->addError($attribute, 'Пользователь уже работает в этой организации');
            return false;
        }

        return true;
    }
}
