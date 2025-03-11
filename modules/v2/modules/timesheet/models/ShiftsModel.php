<?php

namespace app\modules\v2\modules\timesheet\models;

use app\common\components\Pagination;
use app\common\models\UserModel;
use app\common\validators\FullTrimValidator;
use app\models\db\Shifts as ShiftsDB;
use app\models\db\ShiftType;
use app\models\db\VaccinationStation;
use app\modules\v2\modules\timesheet\skeletons\shifts\Delete;
use app\modules\v2\modules\timesheet\skeletons\shifts\Lists;
use app\modules\v2\modules\timesheet\skeletons\shifts\Save;
use app\modules\v2\modules\timesheet\skeletons\shifts\Shift;
use Throwable;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Class ShiftsModel
 *
 * @package app\modules\v2\modules\timesheet\models
 */
class ShiftsModel
{
    /**
     * Метод создает или обновляет указанную смену (если хватает прав)
     *
     * @param string      $fromTime
     * @param int         $duration
     * @param int         $idType
     * @param int         $idOrganization
     * @param string|null $name
     * @param int|null    $id
     * @param int|null    $vaccinationStationId
     * @param int|null    $shiftTypeRefId
     *
     * @return Save
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws Throwable
     */
    public function save(
        string $name = null,
        string $fromTime,
        int $duration,
        int $idType,
        int $idOrganization,
        int $id = null,
        int $vaccinationStationId = null,
        int $shiftTypeRefId = null,
        int $daily = 0
    ): Save {
        if ($id !== null) {
            $model = ShiftsDB::find()->where(['id' => $id])->one();
            if ($model === null) {
                throw new BadRequestHttpException('С данным id нет записей');
            }
            $this->validateAccess($model);
            $model->updated_by = \Yii::$app->user->id;
        } else {
            $model = new ShiftsDB();
            $model->created_by = \Yii::$app->user->id;
        }
        if ($vaccinationStationId !== null) {
            /** @var VaccinationStation $vaccinationStation */
            $vaccinationStation = VaccinationStation::find()->where(['id' => $vaccinationStationId])->one();
            $endTime = date('H:i', strtotime($fromTime) + $duration * 60);
            $name = $vaccinationStation ? $vaccinationStation->short_name.' '.$fromTime.'-'.$endTime : $name;
        }


        $model->name = $name;
        $model->daily = $daily;
        $model->from_time = $fromTime;
        $model->duration = $model->daily == true ? 1440 : $duration;
        $model->id_type = $idType;
        $model->id_organization = $idOrganization;
        $model->vaccination_station_id = $vaccinationStationId;
        $model->shift_type_ref_id = $shiftTypeRefId;

        if (!$model->validate()) {
            $invalidFields = implode(', ', array_keys($model->errors));
            throw new BadRequestHttpException("Следующие переданные параметры невалидны: $invalidFields");
        }

        $model->save(false);

        return new Save();
    }

    /**
     * Метод возвращает список смен для страницы Рабочий график > смены
     *
     * @param int         $orgId
     * @param string|null $name
     * @param array       $shiftTypeId
     * @param int|null    $vaccinationStationId
     * @param int         $page
     * @param int|false   $limit
     * @param int|null    $shiftId
     *
     * @return Lists
     * @throws NotSupportedException
     */
    public function list(
        int $orgId,
        string $name = null,
        array $shiftTypeId = [],
        int $vaccinationStationId = null,
        int $page = 1,
        $limit = 10,
        int $shiftId = null
    ): Lists {
        $shiftTable = ShiftsDB::tableName();
        $query = ShiftsDB::find()
            ->select([
                "{$shiftTable}.id",
                'name',
                'daily',
                "to_char(from_time, 'HH24:MI') AS from_time",
                'duration',
                'id_type',
                'vaccination_station_id',
            ])
            ->where([
                'OR',
                [
                    'AND',
                    ['<>', 'shift_type.type', ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION],
                    ['id_organization' => $orgId],
                ],
                ['shift_type.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION],
            ])
            ->joinWith('type', false);
        $fullTrimValidator = new FullTrimValidator();

        if ($name !== null) {
            $name = $fullTrimValidator->validateValue($name);
            $query->andWhere(['like', 'name', $name]);
        }
        if (count($shiftTypeId)) {
            $query->andWhere(['id_type' => $shiftTypeId]);
        }
        if ($shiftId !== null) {
            $query->andWhere(["{$shiftTable}.id" => $shiftId]);
        }
        if ($vaccinationStationId !== null) {
            $query->andWhere(["{$shiftTable}.vaccination_station_id" => $vaccinationStationId]);
        }

        $totalCount = (clone $query)->count();
        $page = ($limit === false) ? 1 : $page;
        $pagination = new Pagination(
            [
                'totalCount' => $totalCount,
                'defaultPageSize' => 10,
                'pageSize' => ($limit === false ? $totalCount : $limit),
                'page' => $page - 1,
            ]
        );
        if ($limit !== false) {
            $query->offset($pagination->offset)
                ->limit($pagination->limit);
        }
        $models = $query->orderBy('name')
            ->all();

        return new Lists($pagination->pageCount, $models, $pagination->totalCount);
    }

    /**
     * @param int $id
     *
     * @return Shift
     * @throws BadRequestHttpException
     */
    public function getById(int $id): Shift
    {
        $shiftTable = ShiftsDB::tableName();
        $model = ShiftsDB::find()->select([
            'name',
            "to_char(from_time, 'HH24:MI') AS from_time",
            'duration',
            'id_type',
            'vaccination_station_id',
            'daily',
            "{$shiftTable}.id",
        ])
            ->joinWith('type', false)->where(["{$shiftTable}.id" => $id])->one();
        if ($model === null) {
            throw new BadRequestHttpException('С данным id нет записей');
        }

        return new Shift($model);
    }

    /**
     * @param int $id
     *
     * @return Delete
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(int $id): Delete
    {
        $model = ShiftsDB::find()->where(['id' => $id])->one();
        if ($model === null) {
            throw new BadRequestHttpException('С данным id нет записей');
        }
        $this->validateAccess($model);
        $model->delete();

        return new Delete();
    }

    /**
     * Валидация доступа прав
     *
     * @param ActiveRecord $model
     *
     * @throws ForbiddenHttpException
     * @throws Throwable
     */
    private function validateAccess(ActiveRecord $model): void
    {
        /** @var UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        if (!$user->specialist) {
            return;
        }

        if ($user->specialist->id_organization != $model->id_organization) {
            throw new ForbiddenHttpException('У вас нет доступа к смене данной сущности');
        }
    }
}
