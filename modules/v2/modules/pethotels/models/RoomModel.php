<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\PetHotelRoom;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class RoomModel
{
    /**
     * @param $id_pet_hotel
     * @param $purpose
     * @return Query
     * @throws \Throwable
     */
    public function list(
        $id_pet_hotel, $purpose = null
    )
    {
        if (!is_null($purpose)) {
            $filter = ['and',
                ['id_pet_hotel' => $id_pet_hotel],
                ['ilike', 'purpose', $purpose],
            ];
        } else {
            $filter = ['id_pet_hotel' => $id_pet_hotel];
        }
        $query = new Query();
        return $query
            ->select([
                'id', 'name',
                'id_pet_hotel', 'area',
                'notes', 'purpose',
                'created_by', 'updated_by',
                'created_at', 'updated_at',
            ])
            ->from(PetHotelRoom::tableName())
            ->where($filter)
            ->orderBy("name ASC");
    }

    /**
     * @param $id
     * @return PetHotelRoom
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $room = PetHotelRoom::findOne(['id' => $id]);

        if (empty($room)) {
            throw new BadRequestHttpException('Указанная комната зоогостиницы не найдена');
        }

        return $room;
    }

    /**
     * @param $name
     * @param $id_pet_hotel
     * @param $area
     * @param $notes
     * @param $purpose
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search(
        $name, $id_pet_hotel,
        $area, $notes, $purpose,
        $page, $limit
    )
    {
        $filter = ['and'];
        if (!is_null($name)) {
            $filter[] = ['ilike', 'name', $name];
        }
        if (!is_null($id_pet_hotel)) {
            $filter[] = ['id_pet_hotel' => $id_pet_hotel];
        }
        if (!is_null($area)) {
            $filter[] = ['area' => $area];
        }
        if (!is_null($notes)) {
            $filter[] = ['ilike', 'notes', $notes];
        }
        if (!is_null($purpose)) {
            $filter[] = ['ilike', 'purpose', $purpose];
        }
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        /* @var $rooms PetHotelRoom[] */
        $query = PetHotelRoom::find()
            ->andFilterWhere($filter)
            ->orderBy('name ASC');
        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        $rooms = $query->all();
        $pet_hotel_names = [];
        foreach ($rooms as $room) {
            $room_id_pet_hotel = $room->id_pet_hotel;
            if (array_key_exists($room_id_pet_hotel, $pet_hotel_names)) {
                $room->pet_hotel_name = $pet_hotel_names[$room_id_pet_hotel];
            } else {
                $pet_hotel = $room->getPetHotel()->one();
                $pet_hotel_names[$room_id_pet_hotel] = $pet_hotel->name;
                $room->pet_hotel_name = $pet_hotel->name;
            }
        }
        return new CommonList(
            'pet_hotel_room', $rooms,
            $count->count(), $page, $limit
        );
    }

    /**
     * Создает pet_hotel_room
     *
     * @param $name
     * @param $id_pet_hotel
     * @param $area
     * @param $notes
     * @param $purpose
     * @return PetHotelRoom
     * @throws BadRequestHttpException
     */
    public function create(
        $name, $id_pet_hotel,
        $area, $notes, $purpose
    )
    {
        $room = new PetHotelRoom();

        $room->name = $name;
        $room->id_pet_hotel = $id_pet_hotel;
        $room->area = $area;
        $room->notes = $notes;
        $room->purpose = $purpose;

        if (!$room->save()) {
            $errors = $room->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании комнаты зоогостиницы' : implode("\n", array_values($errors)));
        }

        return $room;
    }

    /**
     * Редактирование комнаты зоогостиницы (кроме удаленных)
     *
     * @param $id
     * @param $name
     * @param $id_pet_hotel
     * @param $area
     * @param $notes
     * @param $purpose
     * @return PetHotelRoom
     * @throws BadRequestHttpException
     */
    public function edit(
        $id, $name = null, $id_pet_hotel = null,
        $area = null, $notes = null, $purpose = null
    )
    {
        $room = $this->get($id);

        $room->name = $name ?? $room->name;
        $room->id_pet_hotel = $id_pet_hotel ?? $room->id_pet_hotel;
        $room->area = $area ?? $room->area;
        $room->notes = $notes ?? $room->notes;
        $room->purpose = $purpose ?? $room->purpose;

        if (!$room->save()) {
            $errors = $room->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании комнаты комнаты зоогостиницы' : implode("\n", array_values($errors)));
        }

        return $room;
    }

    /**
     * Помечает зоогостиницу как удаленную
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $room = $this->get($id);

        if (!$room->delete()) {
            $errors = $room->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении комнаты зоогостиницы' : implode("\n", array_values($errors)));
        }
    }

    /**
     * @params $rooms
     * @throws \Throwable
     */
    public function getIdsArray($rooms)
    {
        $rooms_ids = [];
        foreach ($rooms as $room) {
            $rooms_ids[] = $room['id'];
        }
        return $rooms_ids;
    }
}
