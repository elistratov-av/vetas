<?php

namespace app\modules\v2\modules\specialist\models;

use app\common\models\VisitStatus;
use app\models\db\GovServices;
use app\models\db\Shifts;
use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\models\db\Visits;
use app\models\db\VisitsGovServices;
use app\modules\v2\common\models\TimeSheetTrait;
use app\modules\v2\modules\timesheet\skeletons\timesheet\Lists;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class DatelistModel
 * @package app\modules\v2\modules\specialist\models
 */
class DatelistModel extends Model
{
    use TimeSheetTrait;

    /**
     * @var int
     */
    public $id_organization;
    /**
     * @var int
     */
    public $id_shift_type;
    /**
     * @var string тип приема (VISIT | AT_HOME)
     */
    public $type;
    /**
     * @var string
     */
    public $date_from;
    /**
     * @var int
     */
    public $days_count;
    /**
     * @var int
     */
    public $page;
    /**
     * @var int
     */
    public $limit;
    /**
     * @var int id приёма, чтобы получить флаг is_assigned_specialist для спеца, который ранее был назначен на приём
     */
    public $visit_id;
    /**
     * @var array список идентификаторов услуг, которые были выбраны в рамках создания приема
     */
    public $services;

    /**
     * @var array
     */
    private $allowedShiftTypes = [
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT,
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY,
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT,
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE
    ];
    /**
     * @var string
     */
    protected $shiftType;
    /**
     * @var \DateTime
     */
    protected $dateTimeFrom;
    /**
     * @var \DateTime
     */
    protected $dateTimeTo;
    /**
     * флаг для определения, что вызов на дом
     * @var bool
     */
    private $call_to_home;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id_organization', 'id_shift_type', 'date_from'], 'required'],
            [['id_organization', 'id_shift_type', 'days_count', 'page', 'limit', 'visit_id'], 'integer'],
            ['id_shift_type', 'validateShiftType'],
            ['type', 'in', 'range' => [Visits::TYPE_VISIT, Visits::TYPE_AT_HOME]],
            ['type', 'default', 'value' => Visits::TYPE_VISIT],
            ['type', 'validateType'],
            ['date_from', 'validateDateFrom'],
            ['days_count', 'default', 'value' => 14],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 10],
        ];
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateShiftType($attribute, $params, $validator): void
    {
        $shift_type = (new Query())
            ->select('type')
            ->from(ShiftType::tableName())
            ->where(['id' => (int)$this->$attribute])
            ->scalar();

        if (empty($shift_type) || !\in_array($shift_type, $this->allowedShiftTypes, true)) {
            $this->addError($attribute, 'Передан некорректный параметр ' . $attribute);
            return;
        }

        $this->shiftType = $shift_type;
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateType($attribute, $params, $validator): void
    {
        if ($this->$attribute == Visits::TYPE_AT_HOME) {
            $this->call_to_home = true;
        }
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

        $tempDate = clone $dateTimeFrom;
        $dateTimeTo = $tempDate->modify("+ {$this->days_count} days");
        if ($dateTimeFrom === false) {
            $this->addError($attribute, 'Переданная дата невалидна');
            return;
        }

        $this->dateTimeFrom = $dateTimeFrom;
        $this->dateTimeTo = $dateTimeTo;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function list(): Lists
    {
        $dateExpression = $this->prepareDateExpression();
        $timeSheets = $this->findTimeSheets($dateExpression);
        $specialists = [];
        $total = 0;
        $specialistsQuery = $this->prepareSpecialistsQuery();
        $specialistsQuery2 = $this->prepareSpecialistsQuery2();
        $specialists = $this->findSpecialists($specialistsQuery);
        $specialists2 = $this->findSpecialists($specialistsQuery2);
        $specialistsMerge = array_merge($specialists, $specialists2);
        $specIds = [];
        foreach ($specialistsMerge as $item => $value) {
            $specIds[$value['id']]=$this->prepareSpecializationBySpecId($value['id']);
        }
        $this->filterExpelledSpecialists($timeSheets, $specialistsMerge);
        $specId = [];
        foreach ($timeSheets as $k => $v) {
            $specId[] = $v['id_specialist'];
        }
        $specId = array_unique($specId, SORT_STRING);
        $specialistsResult = [];

        foreach ($specialistsMerge as $k => $v) {
            if (in_array($v['id'], $specId)) {
                $specialistsResult[] = $v;
            }
        }
         foreach ($specialistsResult as $k => $v) {
             if (array_key_exists($v['id'], $specIds)) {
                 $specialistsResult[$k]['specializations'] = $specIds[$v['id']];
             }
         }
        $total = count($specialistsResult);
        $result = new Lists($timeSheets, $specialistsResult, $total);
        $result->customPagination(1, $total);

//        if ($this->visit_id != null) {
//            $visit = Visits::find()
//                ->select([
//                    'visits.*',
//                    '(LOWER(VISITS.TIME_RANGE)::date) AS time_visit',
//                ])
//                ->where(['id' => $this->visit_id])
//                ->asArray()
//                ->all();
//            $time_visit = $visit["0"]["time_visit"];
//            $result->time_visit = $time_visit;
//            return $result;
//        }

        return $result;
    }

    /**
     * Возвращает подготовленное условие по времени
     *
     * @return Expression
     */
    protected function prepareDateExpression(): Expression
    {
        $strDateTimeFrom = $this->dateTimeFrom->format('Y-m-d 00:00:00');
        $strDateTimeTo = $this->dateTimeTo->format('Y-m-d 00:00:00');

        return new Expression("(timeshits.date && tsrange('{$strDateTimeFrom}', '{$strDateTimeTo}', '[)'))");
    }

    /**
     * Функция для получения таймшитов специалистов
     *
     * @param Expression $dateExpression
     * @return mixed
     * @throws \yii\db\Exception
     */
    protected function findTimeSheets($dateExpression)
    {
        /*
         * Быстрая правка. ранее что бы записаться на дом необходимо было пересечение записи по телефону и вызова на дом
         * Сейчас такое невозможно, перебиваем на поиск просто записи домой
         */
        if ($this->call_to_home == true && $this->shiftType == ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT) {
            $this->id_shift_type = $this->callToHomeIdShiftType();
        }

        $sql = <<<SQL
SELECT 
     "timesheets"."id",
     "timesheets"."id_specialist",
     lower("timesheets"."date") AS from,
     upper("timesheets"."date") AS to,
     FALSE                      AS manual_shift,
     "shifts"."id_type"         AS "id_shift_type",
     "child"."id"               AS "phone_shift_exists"
FROM "public"."timesheets"
     INNER JOIN "shifts" ON "shifts"."id" = "timesheets"."id_shift"
                              AND "shifts"."id_organization" = :id_organization
     LEFT JOIN (SELECT "tsh"."id", "tsh"."parent_id", "sh"."id_type"
                FROM "timesheets" "tsh"
                       INNER JOIN "shifts" "sh" ON "sh"."id" = "tsh"."id_shift"
                                                     AND "sh"."id_organization" = :id_organization
                                                     AND "sh"."id_type" = :id_shift_type) "child" ON "child"."parent_id" = "timesheets"."id"
WHERE "timesheets"."parent_id" IS NULL
AND ("timesheets"."date" && tsrange(:range_start, :range_end, '[)'))
AND "timesheets"."id_specialist" IN (SELECT "tsh"."id_specialist"
                                     FROM "timesheets" "tsh"
                                            INNER JOIN "shifts" "sh"
                                              ON "sh"."id" = "tsh"."id_shift"
                                              AND "sh"."id_organization" = :id_organization
                                              AND "sh"."id_type" = :id_shift_type
                                              AND ("tsh"."date" && tsrange(:range_start, :range_end, '[)')))
SQL;


        if ($this->shiftType === ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY) {

            $sql .= <<<SQL
 AND "shifts"."id_type" = :id_shift_type
SQL;

            $params = [
                'id_organization' => $this->id_organization,
                'id_shift_type' => $this->id_shift_type,
                'range_start' => $this->dateTimeFrom->format('Y-m-d 00:00:00'),
                'range_end' => $this->dateTimeTo->format('Y-m-d 00:00:00'),
            ];

            $command = Yii::$app->db->createCommand($sql, $params);

            return $command->queryAll();

        }
        $params = [
            'id_organization' => $this->id_organization,
            'id_shift_type' => $this->id_shift_type,
            'range_start' => $this->dateTimeFrom->format('Y-m-d 00:00:00'),
            'range_end' => $this->dateTimeTo->format('Y-m-d 00:00:00'),
            'workday_shift_type' => $this->workdayIdShiftType(),
        ];
        $sqlP = <<<SQL
SELECT * FROM (
SQL;
        $sqlP .= $sql;

        $sqlP .= <<<SQL
) "q"
WHERE "q"."id_shift_type" != :workday_shift_type
   OR ("id_shift_type" = :workday_shift_type AND "phone_shift_exists" NOTNULL)
SQL;

        $sql = $sqlP;

        $command = Yii::$app->db->createCommand($sql, $params);

        return $command->queryAll();
    }

    /**
     * Подготавливает запрос на выборку специалистов
     *
     * @param array $ids
     * @return \yii\db\ActiveQuery
     */
    protected function prepareSpecialistsQuery(): \yii\db\ActiveQuery
    {
        $query = Specialists::find()
            ->select([
                'specialists.id',
                'fullname AS fio',
                'reg_date',
                'expel_date',
            ])
            ->where([
                'specialists.id_organization' => $this->id_organization,
            ])
            ->joinWith('user', false)
            ->andWhere([
                'OR',
                ['expel_date' => null],
                ['>=', 'expel_date', $this->dateTimeFrom->format('Y-m-d')],
            ])
            ->join('RIGHT JOIN', 'users_specializations us', "us.id_user = specialists.id_user");

        if ($this->services) {
            $query->join(
                'RIGHT JOIN',
                'services_specialists ss',
                "ss.id_specialist = specialists.id and ss.id_organization = specialists.id_organization"
            )
            ->andWhere(['IN', 'ss.id_service', $this->services]);
        }

//        $subQuery = (new Query())
//            ->select([
//                'visits.time_range as visitTimeRange',
//                "tsrange(lower(time_range) + '1 day'::interval, upper(time_range) + '1 day'::interval) as nextDayRange",
//                "tsrange(lower(time_range) - '1 day'::interval, upper(time_range) - '1 day'::interval) as previousDayRange"
//            ])
//            ->from('visits')
//            ->where(['=', 'visits.id', $this->visit_id]);
//
//        $query
//            ->addSelect([
//                'CAST(CASE
//                            WHEN vs.id_specialist = specialists.id THEN true
//                            ELSE false
//                          END AS BOOLEAN) AS is_assigned_specialist',
//            ])
//            ->join('LEFT JOIN', 'visits_specialists vs', "vs.id_visit = $this->visit_id")
//            ->innerJoin(['subQuery' => $subQuery], '1 = 1');


        return $query;
    }

    /**
     * Подготавливает запрос на выборку специалистов
     *
     * @param array $ids
     * @return \yii\db\ActiveQuery
     */
    protected function prepareSpecialistsQuery2(): \yii\db\ActiveQuery
    {
        $query = $this->prepareSpecialistsQuery();
        $excludedIds = $query->column(); // получаем список id специалистов из первой выборки
        $query2 = Specialists::find()
            ->select([
                'specialists.id',
                'fullname AS fio',
                'reg_date',
                'expel_date',
            ])
            ->where([
                'specialists.id_organization' => $this->id_organization,
            ])
            ->joinWith('user', false)
            ->andWhere([
                'OR',
                ['expel_date' => null],
                ['>=', 'expel_date', $this->dateTimeFrom->format('Y-m-d')],
            ])
            ->andWhere(['NOT IN', 'specialists.id', $excludedIds]);

        if ($this->services) {
            $query2->join(
                'RIGHT JOIN',
                'services_specialists ss',
                "ss.id_specialist = specialists.id and ss.id_organization = specialists.id_organization"
            )
            ->andWhere(['IN', 'ss.id_service', $this->services]);
        }

        return $query2;
    }

    /**
     * @param \yii\db\ActiveQuery $query
     * @return array
     */
    private
    function findSpecialists($query): array
    {

        $orderBy = [];

        if ($this->visit_id) {
//            $orderBy['is_assigned_specialist'] = SORT_DESC;
//            $orderBy['priority_timesheets'] = SORT_ASC;
//            $orderBy['priority_visits'] = SORT_ASC;
        }
        $orderBy['fullname'] = SORT_ASC;


        $query->orderBy($orderBy);
//            ->limit($this->limit);
//            ->offset($this->limit * ($this->page - 1));

        return $query->asArray()->all();
    }

    /**
     * @return int
     */
    protected
    function workdayIdShiftType(): int
    {
        return (new Query())
            ->select('id')
            ->from(ShiftType::tableName())
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY])
            ->scalar();
    }

    /**
     * @return int
     */
    private
    function callToHomeIdShiftType(): int
    {
        return (new Query())
            ->select('id')
            ->from(ShiftType::tableName())
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME])
            ->scalar();
    }

    private
    function prepareSpecializationBySpecId($id)
    {

        $query = Specialists::find()
            ->select([
                'sp.name',
            ])
            ->where([
                'specialists.id' => $id,
            ])
            ->joinWith('user', false)
            ->join('JOIN', 'users_specializations us', "us.id_user = specialists.id_user")
            ->join('JOIN', 'specializations sp', "us.id_specialization = sp.id");

        return $query->asArray()->all();
    }
}
