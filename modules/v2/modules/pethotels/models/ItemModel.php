<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\PetHotel;
use app\models\db\PetHotelRoom;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

class ItemModel
{
    /**
     * @param $id_organization
     * @return ActiveQuery
     * @throws \Throwable
     */
    public function list($id_organization)
    {
        if (is_null($id_organization)) {
            return PetHotel::find()
                ->orderBy("name ASC");
        } else {
            return PetHotel::find()
                ->where(['id_organization' => $id_organization])
                ->orderBy("name ASC");
        }
    }

    /**
     * @param $id
     * @return PetHotel
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $hotel = PetHotel::findOne(['id' => $id]);

        if (empty($hotel)) {
            throw new BadRequestHttpException('Указанная элемент зоогостиница не найдена');
        }

        return $hotel;
    }

    /**
     * @param $name
     * @param $id_organization
     * @param $page
     * @param $limit
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search($name, $id_organization, $page, $limit)
    {
        $filter = ['and'];
        if (!is_null($name)) {
            $filter[] = ['ilike', 'name', $name];
        }
        if (!is_null($id_organization)) {
            $filter[] = ['id_organization' => $id_organization];
        }
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $query = PetHotel::find()
            ->andFilterWhere($filter)
            ->orderBy('name ASC');
        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'pet_hotel', $query->all(),
            $count->count(), $page, $limit
        );
    }

    /**
     * Создает pet_hotel
     *
     * @param $name
     * @param $id_organization
     * @return PetHotel
     * @throws BadRequestHttpException
     */
    public function create($name, $id_organization)
    {
        $hotel = new PetHotel();

        $hotel->name = $name;
        $hotel->id_organization = $id_organization;

        if (!$hotel->save()) {
            $errors = $hotel->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании зоогостиницы' : implode("\n", array_values($errors)));
        }

        return $hotel;
    }

    /**
     * Редактирование зоогостиницы (кроме удаленных)
     *
     * @param $id
     * @param $name
     * @param $id_organization
     * @return PetHotel
     * @throws BadRequestHttpException
     */
    public function edit($id, $name = null, $id_organization = null)
    {
        $hotel = $this->get($id);

        $hotel->name = $name ?? $hotel->name;
        $hotel->id_organization = $id_organization ?? $hotel->id_organization;

        if (!$hotel->save()) {
            $errors = $hotel->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании зоогостиницы' : implode("\n", array_values($errors)));
        }

        return $hotel;
    }

    /**
     * Помечает зоогостиницу как удаленную
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $hotel = $this->get($id);

        if (!$hotel->delete()) {
            $errors = $hotel->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении зоогостиницы' : implode("\n", array_values($errors)));
        }
    }

    /**
     * @return PetHotelRoom[]
     */
    public static function rooms()
    {
        $pet_hotel = new PetHotel();
        return $pet_hotel->getRooms()->all();
    }

}
