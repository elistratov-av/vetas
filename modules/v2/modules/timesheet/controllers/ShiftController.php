<?php

namespace app\modules\v2\modules\timesheet\controllers;

use app\models\db\Shifts;
use app\models\db\ShiftType;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\timesheet\models\ShiftsModel;
use app\modules\v2\modules\timesheet\skeletons\shifts\Delete;
use app\modules\v2\modules\timesheet\skeletons\shifts\Lists;
use app\modules\v2\modules\timesheet\skeletons\shifts\Save;
use app\modules\v2\modules\timesheet\skeletons\shifts\Shift;
use app\modules\v2\modules\timesheet\skeletons\shifts\Type;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Class ShiftController
 * работа с типами смен
 * https://jira.altarix.ru/browse/VETAIS-792
 *
 * @package app\modules\v2\modules\timesheet\controllers
 */
class ShiftController extends BaseController
{
    /**
     * Получение типов смен
     * https://jira.altarix.ru/browse/VETAIS-792
     *
     * @return Type
     */
    public function actionTypes(): Type
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return new Type(ShiftType::find()->all());
    }

    /**
     * Метод возвращает список смен для страницы Рабочий график > смены
     * https://jira.altarix.ru/browse/VETAIS-796
     *
     * @param int         $id_organization
     * @param string|null $name
     * @param array       $shift_type_id
     * @param int|null    $vaccination_station_id
     * @param int         $page
     * @param int|false   $limit
     * @param int|null    $id
     *
     * @return Lists
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\base\NotSupportedException
     */
    public function actionList(
        int $id_organization,
        string $name = null,
        array $shift_type_id = [],
        int $vaccination_station_id = null,
        int $page = 1,
        $limit = 10,
        int $id = null
    ): Lists {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if ($limit !== false && $limit <= 0) {
            throw new BadRequestHttpException('Параметр limit должен быть больше нуля');
        }
        if ($page <= 0) {
            throw new BadRequestHttpException('Параметр page должен быть больше нуля');
        }
        if ($id !== null && $id <= 0) {
            throw new BadRequestHttpException('Параметр id должен быть больше нуля');
        }
        if ($id !== null && !is_numeric($id)) {
            throw new BadRequestHttpException('Параметр id должен быть числом');
        }
        if ($vaccination_station_id !== null && !is_numeric($vaccination_station_id)) {
            throw new BadRequestHttpException('Параметр vaccination_station_id должен быть числом');
        }

        $shiftModel = new ShiftsModel();

        return $shiftModel->list($id_organization, $name, $shift_type_id, $vaccination_station_id, $page, $limit, $id);
    }

    /**
     * Метод создает или обновляет указанную смену (если хватает прав)
     * https://jira.altarix.ru/browse/VETAIS-803
     *
     * @param string|null $name
     * @param string      $from_time
     * @param int         $duration
     * @param int         $id_type
     * @param int         $id_organization
     * @param int|null    $id
     * @param int|null    $vaccination_station_id
     *
     * @return Save
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws Throwable
     */
    public function actionSave(
        string $from_time,
        int $duration,
        int $id_type,
        int $id_organization,
        string $name = null,
        int $id = null,
        int $vaccination_station_id = null,
        int $daily = 0
    ): Save {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if ($duration <= 0) {
            throw new BadRequestHttpException('Параметр duration должен быть больше нуля');
        }
        if (!preg_match('/^(([0,1]\d)|(2[0-3])):[0-5]\d$\b/', $from_time)) {
            throw new BadRequestHttpException('Параметр from_time имеет невалидный формат');
        }

        $shiftModel = new ShiftsModel();

        return $shiftModel->save(
            $name,
            $from_time,
            $duration,
            $id_type,
            $id_organization,
            $id,
            $vaccination_station_id,
            null,
            $daily
        );
    }

    /**
     * Метод возвращает запрошенную смену.
     * https://jira.altarix.ru/browse/VETAIS-802
     *
     * @param int $id
     *
     * @return Shift
     * @throws BadRequestHttpException
     */
    public function actionGet(int $id): Shift
    {
        if ($id <= 0) {
            throw new BadRequestHttpException('Параметр id должен быть больше нуля');
        }

        $this->checkAccess($this->action->getUniqueId(), Shifts::findOne(['id' => $id]), $this->actionParams);

        $shiftModel = new ShiftsModel();

        return $shiftModel->getById($id);
    }

    /**
     * Метод удаляет указанную смену (если хватает прав)
     * https://jira.altarix.ru/browse/VETAIS-801
     *
     * @param int $id
     *
     * @return Delete
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete(int $id): Delete
    {
        if ($id <= 0) {
            throw new BadRequestHttpException('Параметр id должен быть больше нуля');
        }

        $this->checkAccess($this->action->getUniqueId(), Shifts::findOne(['id' => $id]), $this->actionParams);

        $shiftModel = new ShiftsModel();

        return $shiftModel->delete($id);
    }
}
