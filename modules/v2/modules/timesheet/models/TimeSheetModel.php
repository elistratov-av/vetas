<?php

namespace app\modules\v2\modules\timesheet\models;

use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\modules\v2\common\models\TimeSheetTrait;
use app\modules\v2\modules\timesheet\skeletons\timesheet\Lists;
use phpDocumentor\Reflection\Types\Integer;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class TimeSheetModel
{
    use TimeSheetTrait;

    /**
     * Метод для получения расписаний и врачей
     *
     * @param array $shiftTypeId
     * @param array $specialistId
     * @param string $dateFrom
     * @param int $daysCount
     * @param int $idOrganization
     * @param int $page
     * @param int $limit
     * @return Lists
     * @throws BadRequestHttpException
     */
    public function list(string $dateFrom, int $idOrganization, array $shiftTypeId = [], array $specialistId = [], int $daysCount = 14, int $page = 1, $limit = 10): Lists
    {
        $dateExpression = $this->prepareDateExpression($dateFrom, $daysCount);
        $specialistsQuery = $this->prepareSpecialistsQuery($dateFrom, $dateExpression, $idOrganization, $shiftTypeId, $specialistId);

        $count = (clone $specialistsQuery)->count();

        $page = ($limit === false) ? 1 : $page;
        if ($limit !== false) {
            $specialistsQuery->limit($limit)
                ->offset($limit * ($page - 1));
        }

        $specialists = $this->executeSelectSpecialists($specialistsQuery, $dateExpression);
        $specialistsIds = ArrayHelper::getColumn($specialists, 'id');
        $timeSheets = $this->executeSelectTimeSheets($specialistsIds, $dateExpression, $shiftTypeId);

        if (!empty($timeSheets) && !empty($specialists)) {
            $this->filterExpelledSpecialists($timeSheets, $specialists);

            $shiftsTypes = [];
            $shiftsTypeSql = ShiftType::findBySql("select id, parent_id from shift_type where parent_id is not null")->all();

            foreach ($shiftsTypeSql as $value) $shiftsTypes[$value['id']] = $value['parent_id'];

            $timeSheetExts = [];
            foreach ($timeSheets as $timeSheet) {

                if (!array_key_exists($timeSheet['id_shift_type'], $shiftsTypes)) continue;

                if (in_array($shiftsTypes[$timeSheet['id_shift_type']], $shiftTypeId)) continue;

                $timeSheetExt = $this->executeSelectTimeSheets([$timeSheet['id_specialist']], $dateExpression, [$shiftsTypes[$timeSheet['id_shift_type']]]);
                if (count($timeSheetExt))
                    $timeSheetExts = array_merge($timeSheetExts, $timeSheetExt);
            }

            if (count($timeSheetExts)) {
                $timeSheets = array_merge($timeSheets, $timeSheetExts);
            }
        }

        $result = new Lists($timeSheets, $specialists, $count);
        $result->customPagination($page, ($limit === false ? $count : $limit));

        return $result;
    }

    /**
     * Функция для получения таймшитов специалистов
     *
     * @param array $specialistsIds
     * @param Expression $dateExpression
     * @param integer[]|NULL $shiftTypeId
     * @return mixed
     */
    protected function executeSelectTimeSheets($specialistsIds, $dateExpression, $shiftTypeId)
    {
        $query = Timesheets::find()
            ->select([
                'timesheets.id',
                'timesheets.id_specialist',
                'shifts.id AS id_shift',
                new Expression('lower(date) AS from'),
                new Expression('upper(date) AS to'),
                new Expression('FALSE AS manual_shift'),
                'shifts.id_type AS id_shift_type',
                'timesheets.times',
                'cabinet_types.id AS cabinet_id',
                'cabinet_types.name AS cabinet_name',
                'cabinet_types.description AS cabinet_description',
            ])
            ->innerJoin('shifts', 'shifts.id = timesheets.id_shift')
            ->leftJoin('cabinet_types', 'cabinet_types.id = timesheets.cabinet_type')
            ->andWhere($dateExpression)
            ->andWhere(['IN', 'timesheets.id_specialist', $specialistsIds]);


        if (!empty($shiftTypeId)) {
            $query->andWhere([
                'IN', 'shifts.id_type', (array)$shiftTypeId
            ]);
        }

        return $query
            ->orderBy(new Expression('lower(date)'))
            ->asArray()
            ->all();
    }

    /**
     * Функция для получения времени работы специалиста из данного диапазона
     *
     * @param \yii\db\ActiveQuery $specialistsQuery
     * @param Expression $dateExpression
     * @return array
     */
    protected function executeSelectSpecialists($specialistsQuery, $dateExpression): array
    {
        // Рабочее время
        $workTimeSubQuery = $this->prepareWorkTimesQuery($dateExpression, [
            'AND',
            ['shift_type.idle' => FALSE],
            ['IS', 'shift_type.parent_id', NULL],
        ]);

        // Не рабочее время
        $notWorkTimeSubQuery = $this->prepareWorkTimesQuery($dateExpression, [
            'AND',
            ['shift_type.idle' => TRUE],
            ['NOT', ['shift_type.parent_id' => NULL]],
        ]);

        // Выборка даннных
        $specialistsQuery
            ->select([
                'specialists.id',
                'fio' => 'fullname',
                'f_fio',
                'i_fio',
                'o_fio',
                'reg_date',
                'expel_date',
                "(select STRING_AGG(specializations.name, ';') from specializations left join users_specializations us on specializations.id = us.id_specialization where us.id_user = users.id) AS specialization_name",
                "(select STRING_AGG(specializations.description, ';') from specializations left join users_specializations us on specializations.id = us.id_specialization where us.id_user = users.id) AS specialization_description",
            ])
            ->addSelect(['work_time' => $workTimeSubQuery])
            ->addSelect(['not_work_time' => $notWorkTimeSubQuery]);

        // Запрос обертка основного запроса для подсчета рабочего времени
        $wrapperQuery = (new Query())
            ->select([
                'id',
                'fio',
                'f_fio',
                'i_fio',
                'o_fio',
                'reg_date',
                'expel_date',
                new Expression('COALESCE(work_time, 0) - COALESCE(not_work_time, 0) AS work_time_sum'),
                'specialization_name',
                'specialization_description',
            ])
            ->from(['sub' => $specialistsQuery])
            ->orderBy('fio, specialization_name');

        return $wrapperQuery->all();
    }

    /**
     * Подготавливает запрос на выборку специалистов
     *
     * @param string $dateFrom
     * @param Expression $dateExpression
     * @param int $idOrganization
     * @param integer[] $shiftTypeId
     * @return \yii\db\ActiveQuery
     */
    protected function prepareSpecialistsQuery($dateFrom, $dateExpression, $idOrganization, $shiftTypeId, $specialistId): \yii\db\ActiveQuery
    {
        // Основной запрос
        $query = Specialists::find()
            ->select('specialists.*')
            ->addSelect(Specialists::personalAttributes())
            ->joinWith('user', false)
            ->distinct()
            ->andWhere(['=', Specialists::tableName() . '.id_organization', $idOrganization])
            ->andWhere(['is', Specialists::tableName() . '.expel_date', null])
            ->orderBy('fullname, specialists.id');

        if (!empty($shiftTypeId)) {  // только специалисты, у которых есть нужные диапазоны
            $subQuery = Timesheets::find()
                ->distinct(TRUE)
                ->select('id_specialist')
                ->innerJoin('shifts', 'shifts.id = timesheets.id_shift')
                ->andWhere(['IN', 'shifts.id_type', $shiftTypeId])
                ->andWhere($dateExpression);

            $query->andWhere(['IN', 'specialists.id', $subQuery]);
        }
        if (!empty($specialistId)) {
            $query->andWhere(['IN', 'specialists.id', $specialistId]);
        }

        return $query;
    }

    /**
     * @todo Будет считать неверно после введения manual_shift!
     *
     * @param Expression $dateExpression
     * @param array $filterWhere
     *
     * @return \yii\db\ActiveQuery
     */
    protected function prepareWorkTimesQuery($dateExpression, $filterWhere): \yii\db\ActiveQuery
    {
        return Timesheets::find()
            ->select('SUM(shifts.duration)')
            ->innerJoin('shifts', 'shifts.id = timesheets.id_shift')
            ->innerJoin('shift_type', 'shifts.id_type = shift_type.id')
            ->andWhere(new Expression('timesheets.id_specialist = specialists.id'))
            ->andWhere($dateExpression)
            ->andWhere($filterWhere)
            ->groupBy('timesheets.id_specialist');
    }

    /**
     *
     * Возвращает подготовленное условие по времени
     * @param $dateFrom
     * @param $daysCount
     * @throws BadRequestHttpException
     * @return Expression
     */
    protected function prepareDateExpression($dateFrom, $daysCount): Expression
    {
        $dateTimeFrom = \DateTime::createFromFormat('Y-m-d', $dateFrom);
        $tempDate = clone $dateTimeFrom;
        $dateTimeTo = $tempDate->modify("+ {$daysCount} days");

        $strDateTimeFrom = $dateTimeFrom->format('Y-m-d 00:00:00');
        $strDateTimeTo = $dateTimeTo->format('Y-m-d 00:00:00');

        if ($strDateTimeFrom === false || $strDateTimeTo === false) {
            throw new BadRequestHttpException('Переданная дата невалидна');
        }

        return new Expression("(date && tsrange('{$strDateTimeFrom}', '{$strDateTimeTo}', '[)'))");
    }
}
