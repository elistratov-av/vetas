<?php


namespace app\modules\v2\modules\timesheet\models;


use app\models\db\Timesheets;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class TimesheetsCopy
{

    /**
     * @param string $source_start_date
     * @param int $source_specialist_id
     * @param int $duration
     * @param string $destination_date
     * @param array $destination_specialist_id
     * @param int $id_organization
     *
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     */
    public function copy(
        string $source_start_date,
        int $source_specialist_id,
        int $duration,
        string $destination_date,
        array $destination_specialist_id,
        int $id_organization
    )
    {

        $dateExpressionSource = $this->prepareDateExpression($source_start_date, $duration);
        $dateExpressionDestination = $this->prepareDateExpression($destination_date, $duration);

        $currentDate = new \DateTime('NOW');
        if ($destination_date . ' 00:00:00' < $currentDate->format('Y-m-d') . ' 00:00:00') {
            throw new BadRequestHttpException("Нельзя редактировать график в прошлом");
        }

        $this->checkDestinationRangeAvailible($destination_specialist_id, $dateExpressionDestination);


        foreach ($destination_specialist_id as $idSpecialist) {
            $this->prepareTimesheetsAndCopy($source_specialist_id, $dateExpressionSource, $duration, $idSpecialist,
                $destination_date, $id_organization);
        }
    }

    /**
     * Модифицируем даты
     *
     * @param $dateFrom
     * @param $daysCount
     *
     * @return array
     */
    protected function prepareDateRange($dateFrom, $daysCount): array
    {
        $sourceDateFrom = \DateTime::createFromFormat('Y-m-d', $dateFrom);
        $tempDateSource = clone $sourceDateFrom;
        $sourceDateTo = $tempDateSource->modify("+ {$daysCount} days");

        return [
            'from' => $sourceDateFrom->format('Y-m-d'),
            'to' => $sourceDateTo->format('Y-m-d'),
        ];
    }

    /**
     * Проверяем доступность диапазона дат для вставки
     *
     * @param $destination_specialist_id
     * @param $dateDestinationExpression
     *
     * @return bool
     * @throws BadRequestHttpException
     */
    protected function checkDestinationRangeAvailible($destination_specialist_id, $dateDestinationExpression): bool
    {
        //проверка смен которые вставляем
        $destinationTimeShiftQuery = Timesheets::find()
            ->select(Timesheets::tableName() . '.id')
            ->innerJoin('shifts', 'shifts.id = timesheets.id_shift')
            ->where(['in', 'id_specialist', $destination_specialist_id])
            ->andWhere($dateDestinationExpression);

        if (!empty($destinationTimeShiftQuery->all())) {
            throw new BadRequestHttpException(
                "В указанном диапазоне для копирования уже присутствует рабочий график. Копирование запрещено!"
            );
        }

        return true;
    }

    /**
     * @param $source_specialist_id
     * @param $dateExpressionSource
     * @param $period
     * @param $duration
     * @param $idSpecialist
     * @param $destination_date
     * @param $id_organization
     *
     * @throws BadRequestHttpException
     * @throws Exception
     */
    protected function prepareTimesheetsAndCopy(
        $source_specialist_id,
        $dateExpressionSource,
        $duration,
        $idSpecialist,
        $destination_date,
        $id_organization
    )
    {
        $workDaysShifts = [];
        //массив всех расписаний исходника за запрашиваемый период
        $sourceTimeShifts = Timesheets::find()
            ->select([
                'timesheets.id',
                'timesheets.id_specialist',
                'timesheets.times AS times',
                'timesheets.cabinet_type AS cabinet_type',
                'shifts.id AS id_shift',
                new Expression('lower(date) AS from'),
                new Expression('upper(date) AS to'),
                new Expression('FALSE AS manual_shift'),
                'shifts.id_type AS id_shift_type',
            ])
            ->innerJoin('shifts', 'shifts.id = timesheets.id_shift')
            ->andWhere(['IN', 'timesheets.id_specialist', $source_specialist_id])
            ->andWhere($dateExpressionSource)
            ->groupBy('timesheets.id, shifts.id, id_shift_type')
            ->asArray()->all();

        if (empty($sourceTimeShifts)) {
            throw new BadRequestHttpException("Выбранный диапазон не содержит рабочий график");
        }

        //массив дат исходного расписания
        $sourceDateTimeArray = array_column($sourceTimeShifts, 'from');
        $firstDay = $sourceDateTimeArray[0];

        $i = 0;
        //перебираем даты исходника, вычисляем кол-во дней между каждой датой и датой старта
        foreach ($sourceDateTimeArray as $date) {
            $startCalculationDate = \DateTime::createFromFormat('Y-m-d H:i:s', $firstDay);

            $formatDate = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $destination = \DateTime::createFromFormat('Y-m-d', $destination_date);
            $tempDestination = clone $destination;

            $interval = $formatDate->diff($startCalculationDate, true)->d;
            $dateTimeFromArray = explode(' ', $sourceTimeShifts[$i]['from']);
            $dateTimeToArray = explode(' ', $sourceTimeShifts[$i]['to']);

            $sourceTimeShifts[$i] = [
                'id_specialist' => $idSpecialist,
                'id_shift' => $sourceTimeShifts[$i]['id_shift'],
                'from' => $destination->modify("+ {$interval} days")->format('Y-m-d') . ' ' . $dateTimeFromArray[1],
                'to' => $tempDestination->modify("+ {$interval} days")->format('Y-m-d') . ' ' . $dateTimeToArray[1],
                'manual_shift' => $sourceTimeShifts[$i]['manual_shift'],
                'id_shift_type' => $sourceTimeShifts[$i]['id_shift_type'],
                'times' => json_decode($sourceTimeShifts[$i]['times']),
                'cabinet_type' => $sourceTimeShifts[$i]['cabinet_type'],
            ];

            $i++;
        }

        //сначала создаем рабочие дни - родительские смены
        $j = 0;
        foreach ($sourceDateTimeArray as $date) {
            $startCalculation = \DateTime::createFromFormat('Y-m-d H:i:s', $firstDay);

            $format = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $destination = \DateTime::createFromFormat('Y-m-d', $destination_date);
            $tempDestinationWorkdays = clone $destination;

            $interval = $format->diff($startCalculation, true)->d;
            $dateTimeFromArray = explode(' ', $sourceTimeShifts[$j]['from']);
            $dateTimeToArray = explode(' ', $sourceTimeShifts[$j]['to']);

            if ($sourceTimeShifts[$j]['id_shift_type'] == 1) {
                $workDaysShifts[] = [
                    'id_specialist' => $idSpecialist,
                    'id_shift' => $sourceTimeShifts[$j]['id_shift'],
                    'from' => $destination->modify("+ {$interval} days")->format('Y-m-d') . ' ' . $dateTimeFromArray[1],
                    'to' => $tempDestinationWorkdays->modify("+ {$interval} days")->format('Y-m-d') . ' ' . $dateTimeToArray[1],
                    'manual_shift' => $sourceTimeShifts[$j]['manual_shift'],
                    'id_shift_type' => $sourceTimeShifts[$j]['id_shift_type'],
                    'times' => $sourceTimeShifts[$j]['times'],
                    'cabinet_type' => $sourceTimeShifts[$j]['cabinet_type'],
                ];
            }

            $j++;
        }

        $workDays = [
            'range' => [
                'date_from' => $destination_date,
                'days_count' => $duration
            ],
            'specialists' => [$idSpecialist],
            'timesheet' => $workDaysShifts,
            'id_organization' => $id_organization
        ];
        Timesheets::getDb()->beginTransaction();

        $this->internalSaveTimesheet($workDays);

        //потом создаем дочерние смены
        $dataToCopy = [
            'range' => [
                'date_from' => $destination_date,
                'days_count' => $duration
            ],
            'specialists' => [$idSpecialist],
            'timesheet' => $sourceTimeShifts,
            'id_organization' => $id_organization
        ];

        $this->internalSaveTimesheet($dataToCopy);

        if (!empty(Timesheets::getDb()->transaction)) {
            Timesheets::getDb()->transaction->commit();
        }
    }

    /**
     * @param $dataToCopy
     * @throws BadRequestHttpException
     * @throws Exception
     */
    protected function internalSaveTimesheet($dataToCopy)
    {
        $timesheetsSave = new TimesheetsSave();
        $timesheetsSave->load($dataToCopy, '');
        $timesheetsSave->save();

        if ($timesheetsSave->hasErrors()) {
            if (!empty(Timesheets::getDb()->transaction)) {
                Timesheets::getDb()->transaction->rollBack();
            }
            $errors = $timesheetsSave->getErrorSummary(true);
            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors))
            );
        }
    }

    /**
     * @param $dateFrom
     * @param $daysCount
     *
     * @return Expression
     * @throws BadRequestHttpException
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
