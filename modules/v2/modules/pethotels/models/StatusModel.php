<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\PetHotelRequestStatus;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

class StatusModel
{
    /**
     * @return ActiveQuery
     * @throws \Throwable
     */
    public function list()
    {
        return PetHotelRequestStatus::find()
            ->orderBy("name ASC");
    }

    /**
     * @param $id
     * @return PetHotelRequestStatus
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $status = PetHotelRequestStatus::findOne(['id' => $id]);

        if (empty($status)) {
            throw new BadRequestHttpException('Указанный статус не найден');
        }

        return $status;
    }

    /**
     * @param $code
     * @param $name
     * @param $page
     * @param $limit
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search(
        $code, $name,
        $page, $limit
    )
    {
        $filter = ['and'];
        if (!is_null($code)) {
            $filter[] = ['code' => $code];
        }
        if (!is_null($name)) {
            $filter[] = ['ilike', 'name', $name];
        }
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $query = PetHotelRequestStatus::find()
            ->andFilterWhere($filter)
            ->orderBy('name ASC');
        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'pet_hotel_request_status', $query->all(),
            $count->count(), $page, $limit
        );
    }

    /**
     * Создает статус
     *
     * @param $code
     * @param $name
     * @return PetHotelRequestStatus
     * @throws BadRequestHttpException
     */
    public function create(
        $code, $name
    )
    {
        $status = new PetHotelRequestStatus();

        $status->code = $code;
        $status->name = $name;

        if (!$status->save()) {
            $errors = $status->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании статуса' : implode("\n", array_values($errors)));
        }

        return $status;
    }

    /**
     * Редактирование статуса (кроме удаленных)
     *
     * @param $code
     * @param $name
     * @return PetHotelRequestStatus
     * @throws BadRequestHttpException
     */
    public function edit(
        $id, $code = null, $name = null
    )
    {
        $status = $this->get($id);

        $status->code = $code ?? $status->code;
        $status->name = $name ?? $status->name;

        if (!$status->save()) {
            $errors = $status->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании статуса' : implode("\n", array_values($errors)));
        }

        return $status;
    }

    /**
     * Помечает статуса как удаленного
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $status = $this->get($id);

        if (!$status->delete()) {
            $errors = $status->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении статуса' : implode("\n", array_values($errors)));
        }
    }

}
