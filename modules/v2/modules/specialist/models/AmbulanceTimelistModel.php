<?php

namespace app\modules\v2\modules\specialist\models;

use app\common\components\rbac\Role;
use app\common\models\VisitStatus;
use app\models\db\GovServices;
use app\models\db\Organizations;
use app\models\db\Shifts;
use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\models\db\Visits;
use app\modules\v2\modules\specialist\skeletons\specialist\Specialist;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class AmbulanceTimelistModel
 * @package app\modules\v2\modules\specialist\models
 *
 * @property int|array $id_organization
 * @property int       $id_shift_type
 * @property string    $date_from
 * @property array     $services
 * @property string    $shiftType
 * @property \DateTime $dateTimeFrom
 * @property \DateTime $dateTimeTo
 *
 * @method validateServices($attribute, $params, $validator): void
 */
class AmbulanceTimelistModel extends DatelistModel
{
    /**
     * @var int
     */
    public $id_specialist;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->shiftType = ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE;
        $this->id_shift_type = ShiftType::getTypesIdWithAmbulance();

        if (empty($this->id_shift_type)) {
            throw new InvalidConfigException('Не назначен тип смены для приемов ВПД');
        }

        if (!empty($this->id_organization) && is_int($this->id_organization)) {
            // получим все id организаций сети по переданному id одной из организаций сети
            $this->id_organization = Organizations::orgTreeIds($this->id_organization);
        }

        $this->date_from = $this->date_from ?? date('Y-m-d');
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id_specialist', 'date_from'], 'required'],
            ['id_specialist', 'integer'],
            ['date_from', 'validateDateFrom'],
            ['id_specialist', 'validateSpecialist'],
            ['services', 'validateServices'],
        ];
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateDateFrom($attribute, $params, $validator): void
    {
        $dateTimeFrom = \DateTime::createFromFormat('Y-m-d', $this->date_from);
        if ($dateTimeFrom === false) {
            $this->addError($attribute, 'Переданная дата невалидна');
            return;
        }

        $this->dateTimeFrom = $dateTimeFrom;
        $this->dateTimeTo = (clone $dateTimeFrom);
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateSpecialist($attribute, $params, $validator): void
    {
        $specialist = Specialists::findOne(['id' => $this->$attribute]);
        if ($specialist === null) {
            $this->addError($attribute, 'Специалист не найден');
            return;
        }
        if (empty($specialist->id_organization) || $specialist->organization === null) {
            $this->addError($attribute, 'Специалисту не назначена организация');
            return;
        }

        if (!empty($this->id_organization) && !in_array($specialist->id_organization, $this->id_organization)) {
            $this->addError($attribute, 'Организация специалиста не относится к вашей организационной структуре');
            return;
        }

        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->getAuthManager();
        $role = $auth->getAssignment(Role::ROLE_VET_SPECIALIST_GOS_AMB, $specialist->id_user, $specialist->id);
        if ($role === null) {
            $this->addError($attribute, 'Специалисту не назначена роль врача ВПД');
            return;
        }
    }

    /**
     * @return \app\modules\v2\modules\specialist\skeletons\specialist\Specialist
     */
    public function timelist()
    {
        /* @var $specialist \app\models\db\Specialists */
        $specialist = Specialists::find()
            ->select(['specialists.id', 'id_user'])
            ->addSelect(Specialists::personalAttributes())
            ->joinWith('user', false)
            ->where(['specialists.id' => $this->id_specialist])
            ->one();

        if ($specialist->isExpelledAtDate($this->date_from)) {
            // специалист уже уволен на текущую дату, дальше ничего не ищем и не проверяем
            // возвращаем пустой массив вместо слотов
            return new Specialist(
                $specialist->toArray(),
                []
            );
        }

        $dateStart = $this->dateTimeFrom->format('Y-m-d 00:00:00');
        $dateEnd = $this->dateTimeFrom->format('Y-m-d 23:59:59');

        $timesheetsDateExpression = new Expression(
            "(timesheets.date && tsrange('{$dateStart}', '{$dateEnd}', '[)'))"
        );

        $workdayTimeSheet = $this->prepareTimesheetsQuery(
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY,
            $timesheetsDateExpression)
            ->one();

        if (!$workdayTimeSheet) {
            // throw new BadRequestHttpException('Рабочий день специалиста не найден');
            return new Specialist(
                $specialist->toArray(),
                []
            );
        }

        $ambulanceTimeSheets = $this->prepareTimesheetsQuery(
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE,
            $timesheetsDateExpression)
            ->andWhere(['timesheets.parent_id' => $workdayTimeSheet['id']])
            ->all();

        if (!$ambulanceTimeSheets) {
            // throw new BadRequestHttpException('В указанный день у специалиста нет расписаний НВП');
            return new Specialist(
                $specialist->toArray(),
                []
            );
        }

        $idleTimeSheets = $this->findChildTimesheetsIdle($workdayTimeSheet['id']);
        $visits = $this->findVisits(new Expression("(visits.time_range && '{$workdayTimeSheet['date']}'::tsrange)"));

        $dateLower = new \DateTime($workdayTimeSheet['date_lower']);
        $dateUpper = new \DateTime($workdayTimeSheet['date_upper']);

        $period = new \DatePeriod(
            $dateLower,
            new \DateInterval('PT10M'),
            $dateUpper
        );
        $slots = [];

        foreach ($period as $slot) {
            /* @var $slot \DateTime */
            $status = SpecialistModel::TIMESLOT_UNAVAILABLE;
            if ($slot->getTimestamp() < time() || $specialist->isExpelledAtDate($slot->format('Y-m-d'))) {
                $status = SpecialistModel::TIMESLOT_UNAVAILABLE;
            } else {
                foreach ($ambulanceTimeSheets as $timesheet) {
                    $timeSheetDateLower = new \DateTime($timesheet['date_lower']);
                    $timeSheetDateUpper = new \DateTime($timesheet['date_upper']);
                    if ($slot >= $timeSheetDateLower && $slot < $timeSheetDateUpper) {
                        $status = SpecialistModel::TIMESLOT_FREE;
                        break;
                    }
                }
                foreach ($idleTimeSheets as $idle) {
                    if ($slot >= new \DateTime($idle['date_lower']) && $slot < new \DateTime($idle['date_upper'])) {
                        $status = SpecialistModel::TIMESLOT_UNAVAILABLE;
                        break;
                    }
                }
            }

            foreach ($visits as $visit) {
                if ($slot >= new \DateTime($visit['time_lower']) && $slot < new \DateTime($visit['time_upper'])) {
                    $status = SpecialistModel::TIMESLOT_VISIT;
                    break;
                }
            }

            $slots[] = [
                'time' => $slot->format('H:i'),
                'date' => $slot->format('Y-m-d'),
                'status' => $status
            ];
        }

        if (!empty($this->services)) {
            // Вычеркиваем недоступные по продолжительности (INSUFFICIENT_DURATION)
            $slots = $this->markShortIntervals($slots);
        }

        return new Specialist(
            $specialist->toArray(),
            $slots
        );
    }

    /**
     * @param string      $shiftType
     * @param Expression  $timesheetsDateExpression
     * @return Query
     */
    private function prepareTimesheetsQuery($shiftType, $timesheetsDateExpression): Query
    {
        return (new Query())
            ->from(Timesheets::tableName(). ' timesheets')
            ->select(
                new Expression('timesheets.*, 
                lower(timesheets.date) AS date_lower,
                upper(timesheets.date) AS date_upper')
            )
            ->leftJoin(Shifts::tableName() . ' shifts', 'shifts.id = timesheets.id_shift')
            ->leftJoin(ShiftType::tableName() . ' shift_type', 'shifts.id_type = shift_type.id AND shift_type.type = :shift_type', ['shift_type' => $shiftType])
            ->where([
                'AND',
                ['timesheets.id_specialist' => $this->id_specialist],
                ['shift_type.type' => $shiftType],
                $timesheetsDateExpression
            ]);
    }

    /**
     * @param int $idParent
     * @return array
     */
    private function findChildTimesheetsIdle($idParent)
    {
        return (new Query())
            ->from(Timesheets::tableName() . ' timesheets')
            ->select(
                new Expression('timesheets.*, 
                lower(timesheets.date) AS date_lower,
                upper(timesheets.date) AS date_upper')
            )
            ->leftJoin(Shifts::tableName() . ' shifts', 'shifts.id = timesheets.id_shift')
            ->where([
                'AND',
                ['timesheets.parent_id' => $idParent],
                ['timesheets.id_specialist' => $this->id_specialist],
            ])
            ->leftJoin(ShiftType::tableName() . ' shift_type', 'shifts.id_type = shift_type.id')
            ->andWhere([
                'shift_type.idle' => true
            ])
            ->all();
    }

    /**
     * @param Expression  $dateExpression
     * @return array
     */
    private function findVisits($dateExpression): array
    {
        return (new Query)->from(Visits::tableName() . ' visits')
            ->select(
                new Expression('visits.*,
                lower(time_range) AS time_lower,
                upper(time_range) AS time_upper')
            )
            ->leftJoin('visits_specialists', 'visits_specialists.id_visit = visits.id')
            ->where([
                'AND',
                ['visits_specialists.id_specialist' => $this->id_specialist],
                ['NOT IN', 'status', [VisitStatus::CANCELED, VisitStatus::TRANSFER]],
                $dateExpression
            ])
            ->all();
    }

    /**
     * @param array $slots
     * @return array
     */
    private function markShortIntervals(array $slots)
    {
        $countSlots = $this->calculateTotalSlots();
        if ($countSlots == 0) {
            return $slots;
        }

        // @see \app\modules\v2\modules\specialist\models\SpecialistModel::markShortIntervals
        $reversedSlots = array_reverse($slots);
        $excludedCount = 0;
        foreach ($reversedSlots as &$slot) {
            if ($slot['status'] !== SpecialistModel::TIMESLOT_FREE) {
                $excludedCount = 0; // Интервал котоый мы могли вычеркивать - уже закончился, обнуляем
                continue;
            }
            /**
             * Примечение: для записи на 2 слота,
             * мы должны пометить только один последний как недоступный
             */
            if ($excludedCount > ($countSlots - 2)) {
                continue;
            }
            /**
             * Начинаем вычеркивать слоты, которых не хватит под указанные услуги
             */
            $slot['status'] = SpecialistModel::TIMESLOT_INSUFFICIENT_DURATION;
            $excludedCount++;
        }
        unset($slot);

        return array_reverse($reversedSlots);
    }

    /**
     * @return int
     */
    private function calculateTotalSlots()
    {
        $total = 0;
        $govServices = GovServices::find()
            ->where(['in', 'id', array_unique(ArrayHelper::getColumn($this->services, 'id'))])
            ->indexBy('id')
            ->all();

        foreach ($this->services as $service) {
            /* @var \app\models\db\GovServices $govService */
            $govService = ArrayHelper::getValue($govServices, $service['id']);
            $total += $govService->calcCountSlots(ArrayHelper::getValue($service, 'count'));
        }

        return $total;
    }
}
