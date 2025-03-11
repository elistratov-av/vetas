<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\models\db\PetHotelRequestStatus;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\RequestModel;
use app\modules\v2\modules\pethotels\models\RoomModel;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class RoomController extends BaseController
{

    /**
     * Возвращает список всех помещений зоогостиницы
     * @param $id_pet_hotel
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll($id_pet_hotel)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RoomModel();
        return [
            'result' => $model->list($id_pet_hotel)->all(),
        ];
    }

    /**
     * Создает новую комнату зоогостиницы
     *
     * @param $name
     * @param $id_pet_hotel
     * @param $area
     * @param $notes
     * @param $purpose
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate(
        $name, $id_pet_hotel,
        $area, $purpose, $notes = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RoomModel();
        $hotel = $model->create($name, $id_pet_hotel, $area, $notes, $purpose);
        return [
            'result' => true,
            'id' => $hotel->id,
        ];
    }

    /**
     * Удаляет комнату зоогостиницы
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RoomModel();
        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование комнаты зоогостиницы
     *
     * @param $id
     * @param $name
     * @param $id_pet_hotel
     * @param $area
     * @param $notes
     * @param $purpose
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit(
        $id, $name = null, $id_pet_hotel = null,
        $area = null, $notes = null, $purpose = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RoomModel();
        $model->edit($id, $name, $id_pet_hotel, $area, $notes, $purpose);
        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанную комнату зоогостиницы
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RoomModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Поиск комнат зоогостиниц
     *
     * @param $name
     * @param $id_pet_hotel
     * @param $area
     * @param $notes
     * @param $purpose
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $name = null, $id_pet_hotel = null,
        $area = null, $notes = null, $purpose = null,
        $page = null, $limit = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $model = new RoomModel();
        return [
            'result' => $model->search(
                $name, $id_pet_hotel,
                $area, $notes, $purpose,
                $page, $limit
            ),
        ];
    }

    /**
     * Получить свободные помещения
     *
     * @return array[]
     * @throws \Throwable
     */
    public function actionFree(
        $id_hotel, $date_from,
        $date_to, $purpose = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model_room = new RoomModel();
        $rooms = $model_room->list($id_hotel, $purpose)->all();
        $room_ids = $model_room->getIdsArray($rooms);

        $model_request = new RequestModel();
        $requests = $model_request
            ->selectQuery()
            ->where(['and',
                ['phr.id_room' => $room_ids],
                ['or',
                    ['and',
                        ['<=', 'date(phr.date_from)', new Expression('date(\'' . $date_from . '\')')],
                        ['>=', 'date(phr.date_to)', new Expression('date(\'' . $date_from . '\')')]
                    ],
                    ['and',
                        ['>=', 'date(phr.date_to)', new Expression('date(\'' . $date_to . '\')')],
                        ['<=', 'date(phr.date_from)', new Expression('date(\'' . $date_to . '\')')]
                    ],
                ]
            ])
            ->all();

        $statuses = PetHotelRequestStatus::find()
            ->where(['in', 'code', [
                PetHotelRequestStatus::CODE_ACTIVE,
                PetHotelRequestStatus::CODE_BOOKED,
                PetHotelRequestStatus::CODE_CONFIRMED_BOOKED,
            ]])
            ->all();

        $statuses = ArrayHelper::map($statuses, 'code', 'id');

        if (empty($requests)) {
            return [
                'result' => $rooms,
            ];
        }
        $busy_rooms = [];
        foreach ($requests as $request) {
            if (in_array($request['id_status'], $statuses)) {
                $room_id = $request['id_room'];
                $busy_rooms[$room_id] = $room_id;
            }
        }

        $result = [];
        foreach ($rooms as $room) {
            if (empty($busy_rooms[$room['id']])) {
                $result[] = $room;
            }
        }

        return [
            'result' => $result,
        ];
    }

}
