<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\PetHotel;
use app\models\db\PetHotelRequest;
use app\models\db\PetHotelRequestStatus;
use app\models\db\PetHotelRoom;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class RequestModel
{

    /**
     * @param $room_ids
     * @param $date_from
     * @param $date_to
     * @return Query
     * @throws \Throwable
     */
    public function list(
        $room_ids = null, $date_from = null, $date_to = null
    )
    {
        if (is_null($date_from)) {
            if (!is_null($date_to)) {
                $date_from = date("Y-m-01", strtotime($date_to));
            } else {
                $date_from = date('Y-m-01');
            }
        }
        if (is_null($date_to)) {
            if (!is_null($date_from)) {
                $date_to = date('Y-m-t', strtotime($date_from));
            } else {
                $date_to = date('Y-m-t');
            }
        }
        $filter = ['or',
            ['and',
                ['>=', 'phr.date_from', $date_from],
                ['<=', 'phr.date_from', $date_to]
            ],
            ['and',
                ['>=', 'phr.date_to', $date_from],
                ['<=', 'phr.date_to', $date_to]
            ],
        ];
        if (!is_null($room_ids)) {
            $filter = ['and',
                $filter,
                ['phr.id_room' => $room_ids]
            ];
        }
        return $this->selectQuery()
            ->filterWhere($filter)
            ->orderBy('phr.date_from ASC, phr.id ASC');
    }

    /**
     * @param $room_ids
     * @param $date_from
     * @param $date_to
     * @return Query
     * @throws \Throwable
     */
    public function listBusy(
        $room_ids = null, $date_from = null, $date_to = null
    )
    {
        if (is_null($date_from)) {
            if (!is_null($date_to)) {
                $date_from = date("Y-m-01", strtotime($date_to));
            } else {
                $date_from = date('Y-m-01');
            }
        }
        if (is_null($date_to)) {
            if (!is_null($date_from)) {
                $date_to = date('Y-m-t', strtotime($date_from));
            } else {
                $date_to = date('Y-m-t');
            }
        }
        $filter = ['or',
            ['and',
                ['<=', 'phr.date_from', $date_from],
                ['>=', 'phr.date_to', $date_from]
            ],
            ['and',
                ['<=', 'phr.date_from', $date_to],
                ['>=', 'phr.date_to', $date_to]
            ],
        ];
        if (!is_null($room_ids)) {
            $filter = ['and',
                $filter,
                ['phr.id_room' => $room_ids]
            ];
        }
        return $this->selectQuery()
            ->filterWhere($filter)
            ->orderBy('phr.date_from ASC, phr.id ASC');
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $searchQuery = $this->searchQuery();
        $request = $searchQuery->andWhere(['phr.id' => $id])->one();

        if (empty($request)) {
            throw new BadRequestHttpException('Указанная заявка не найдена');
        }

        return $request;
    }

    public function selectQuery()
    {
        $query = new Query();
        return $query
            ->select([
                'phr.id', 'phr.id_status', 'phr.id_owner', 'phr.id_animal', 'phr.id_room',
                'phr.date_from', 'phr.date_to', 'phr.animal_weight', 'phr.count_feeding',
                'phr.animal_diet_dry', 'phr.animal_diet_wet', 'phr.feeding_rate', 'phr.count_walking', 'phr.notes',
                'phr.created_by', 'phr.updated_by', 'phr.created_at', 'phr.updated_at',
                'p.name as animal_nickname', 's.name as animal_type',
                'b.name as animal_breed', 'pha.processing as animal_processing',
                new Expression('extract(years from justify_interval(now()-p.birthday))::int as animal_age'),
                'concat_ws(\' \', po.i_fio, po.o_fio, po.f_fio) as owner_fio',
                new Expression('coalesce(phao.phone_number, c.name) as owner_phone'),
                'phao.address as owner_address',
                'phrs.code as status_name', 'phr2.name as room_name',
                'ph.name as hotel_name'
            ])
            ->from(
                PetHotelRequest::tableName() . ' phr'
            )
            ->leftJoin(
                'pet_hotel_animal pha',
                'phr.id_animal=pha.id_pet'
            )
            ->leftJoin(
                'pets p',
                'p.id=phr.id_animal'
            )
            ->leftJoin(
                'pet_owners po',
                'po.id=phr.id_owner'
            )
            ->leftJoin(
                'pet_hotel_animal_owner phao',
                'phao.id_owner=phr.id_owner'
            )
            ->leftJoin(
                'breeds b',
                'b.id=p.id_breed'
            )
            ->leftJoin(
                'species s',
                's.id=b.species_id'
            )
            ->leftJoin(
                PetHotelRoom::tableName() . ' phr2',
                'phr.id_room=phr2.id'
            )
            ->leftJoin(
                PetHotelRequestStatus::tableName() . ' phrs',
                'phr.id_status=phrs.id'
            )
            ->leftJoin(
                PetHotel::tableName() . ' ph',
                'phr2.id_pet_hotel=ph.id'
            )
            ->leftJoin('public.contacts c', 'c.entity_id=po.id')
            ->leftJoin('public.contact_types ct', 'c.id_contact_type=ct.id')
            ->andWhere(['and',
                ['or', ['ct.type' => null], ['ct.type' => 'phone']],
                ['or', ['ct.entity_type' => null], ['ct.entity_type' => 'pet_owner']],
            ]);
    }

    /**
     * @return Query
     * @throws BadRequestHttpException
     */
    public function searchQuery(
        $id_status = null, $id_owner = null, $id_animal = null,
        $id_room = null, $id_hotel = null,
        $date_created = null, $date_from = null, $date_to = null,
        $notes = null, $owner_fio = null, $owner_phone = null,
        $animal_type = null, $animal_nickname = null,
        $room_name = null
    )
    {
        $filter = ['and'];
        if (!is_null($id_status)) {
            $filter[] = ['phr.id_status' => $id_status];
        }
        if (!is_null($id_owner)) {
            $filter[] = ['phr.id_owner' => $id_owner];
        }
        if (!is_null($id_animal)) {
            $filter[] = ['phr.id_animal' => $id_animal];
        }
        if (!is_null($id_hotel)) {
            $model = new ItemModel();
            $hotel = $model->get($id_hotel);
            /* @var $rooms PetHotelRoom[] */
            $rooms = $hotel->getRooms()->all();
            $rooms_ids = [];
            foreach ($rooms as $room) {
                $rooms_ids[] = $room->id;
            }
            $filter[] = ['phr.id_room' => $rooms_ids];
        }
        if (!is_null($id_room)) {
            $filter[] = ['phr.id_room' => $id_room];
        }
        if (!is_null($notes)) {
            $filter[] = ['ilike', 'phr.notes', $notes];
        }
        if (!is_null($owner_fio)) {
            $filter[] = ['ilike', 'concat_ws(\' \', po.i_fio, po.o_fio, po.f_fio)', $owner_fio];
        }
        if (!is_null($owner_phone)) {
            $filter[] = ['ilike', 'phao.phone_number', $owner_phone];
        }
        if (!is_null($animal_type)) {
            $filter[] = ['ilike', 's.name', $animal_type];
        }
        if (!is_null($animal_nickname)) {
            $filter[] = ['ilike', 'p.name', $animal_nickname];
        }
        if (!is_null($room_name)) {
            $filter[] = ['ilike', 'phr2.name', $room_name];
        }
        if (!is_null($date_created)) {
            $filter[] = [
                'date(phr.created_at)' => new Expression(
                    'date(\'' . $date_created . '\')'
                ),
            ];
        }
        if (!is_null($date_from)) {
            $filter[] = ['>=', 'phr.date_from', $date_from];
        }
        if (!is_null($date_to)) {
            $filter[] = ['<=', 'phr.date_to', $date_to];
        }
        return $this->selectQuery()
            ->andFilterWhere($filter)
            ->orderBy('phr.date_from ASC, phr.id ASC');
    }

    /**
     * @param $searchQuery
     * @param $page
     * @param $limit
     * @return CommonList
     */
    public function search(
        $searchQuery, $page = null, $limit = null
    )
    {
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $count = clone $searchQuery;
        $searchQuery = $searchQuery
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'pet_hotel_request', $searchQuery->all(),
            $count->count(), $page, $limit
        );
    }

    /**
     * Создает заявку
     *
     * @param $id_status
     * @param $id_owner
     * @param $id_animal
     * @param $id_room
     * @param $date_from
     * @param $date_to
     * @param $notes
     * @return PetHotelRequest
     * @throws BadRequestHttpException
     */
    public function create(
        $id_status, $id_owner, $id_animal, $id_room,
        $date_from, $date_to, $notes
    )
    {
        $request = new PetHotelRequest();

        $request->id_status = $id_status;
        $request->id_owner = $id_owner;
        $request->id_animal = $id_animal;
        $request->id_room = $id_room;
        $request->date_from = $date_from;
        $request->date_to = $date_to;
        $request->notes = $notes;

        if (!$request->save()) {
            $errors = $request->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании заявки' : implode("\n", array_values($errors)));
        }

        return $request;
    }

    // Памятка
    public function jotting($id, $animal_weight, $count_feeding, $animal_diet_dry, $animal_diet_wet, $feeding_rate, $count_walking, $notes) {
        $request = PetHotelRequest::findOne(['id' => $id]);

        if (empty($request)) {
            throw new BadRequestHttpException('Указанная заявка не найдена');
        }

        $request->animal_weight = $animal_weight;
        $request->count_feeding = $count_feeding;
        $request->animal_diet_dry = $animal_diet_dry;
        $request->animal_diet_wet = $animal_diet_wet;
        $request->feeding_rate = $feeding_rate;
        $request->count_walking = $count_walking;
        $request->notes = $notes;

        $request->save(false);

        return $request;
    }

    // продление заявки
    public function prolong($id, $date_to) {
        $request = PetHotelRequest::findOne(['id' => $id]);
        $statuses = PetHotelRequestStatus::find()
            ->where(['in' ,'code', ['Активно', 'Забронировано', 'Бронирование']])
            ->all();

        $statusesArray = ArrayHelper::map($statuses, 'code', 'id');

        if (empty($request)) {
            throw new BadRequestHttpException('Указанная заявка не найдена');
        }

        if (empty($statuses)) {
            throw new BadRequestHttpException('Статус не найден');
        }

        if ($request->date_from > $date_to ) {
            throw new BadRequestHttpException('Указанная дата меньше Даты от');
        }

        if ($request->date_to > $date_to ) {
            throw new BadRequestHttpException('Указанная дата меньше Даты до');
        }

        $busyRequest1 = PetHotelRequest::find()
            ->where(['<=', 'date_from', $date_to])
            ->andWhere(['>=', 'date_to', $date_to])
            ->andWhere(['id_room' => $request->id_room])
            ->andWhere(['in', 'id_status', $statusesArray])
            ->one();

        if (!empty($busyRequest1)) {
            throw new BadRequestHttpException('Помещение занятно с: ' .
                date('d-m-Y', strtotime($busyRequest1->date_from)) . ' по ' .
                date('d-m-Y', strtotime($busyRequest1->date_to)) . '. Выберите другую дату или создайте новую заявку на бронирование');
        }

        $busyRequest2 = PetHotelRequest::find()
            ->where(['>=', 'date_from', date('Y-m-d H:i:s')])
            ->andWhere(['<=', 'date_to', $date_to])
            ->andWhere(['id_room' => $request->id_room])
            ->andWhere(['in', 'id_status', $statusesArray])
            ->one();

        if (!empty($busyRequest2)) {
            throw new BadRequestHttpException('Помещение занятно с: ' .
                date('d-m-Y', strtotime($busyRequest2->date_from)) . ' по ' .
                date('d-m-Y', strtotime($busyRequest2->date_to)) . '. Выберите другую дату или создайте новую заявку на бронирование');
        }

        $request->date_to = $date_to;

        $request->save(false);

        return $request;
    }

    /**
     * Редактирование заявки (кроме удаленных)
     *
     * @param $id_status
     * @param $id_owner
     * @param $id_animal
     * @param $id_room
     * @param $date_from
     * @param $date_to
     * @param $notes
     * @return PetHotelRequest
     * @throws BadRequestHttpException
     */
    public function edit(
        $id, $id_status = null, $id_owner = null, $id_animal = null, $id_room = null,
        $date_from = null, $date_to = null, $notes = null,
        $animal_weight = null, $count_feeding = null, $animal_diet_dry = null, $animal_diet_wet = null, $feeding_rate = null, $count_walking = null
    )
    {
        $request = PetHotelRequest::findOne(['id' => $id]);

        $request->id_status = $id_status ?? $request->id_status;
        $request->id_owner = $id_owner ?? $request->id_owner;
        $request->id_animal = $id_animal ?? $request->id_animal;
        $request->id_room = $id_room ?? $request->id_room;
        $request->date_from = $date_from ?? $request->date_from;
        $request->date_to = $date_to ?? $request->date_to;
        $request->notes = $notes ?? $request->notes;
        $request->animal_weight = $animal_weight;
        $request->count_feeding = $count_feeding;
        $request->animal_diet_dry = $animal_diet_dry;
        $request->animal_diet_wet = $animal_diet_wet;
        $request->feeding_rate = $feeding_rate;
        $request->count_walking = $count_walking;

        if (mb_strlen($request->date_from, 'UTF-8') > 10) {
            $request->date_from = substr($request->date_from, 0, 10);
        }
        if (mb_strlen($request->date_to, 'UTF-8') > 10) {
            $request->date_to = substr($request->date_to, 0, 10);
        }

        if (!$request->save()) {
            $errors = $request->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании заявки' : implode("\n", array_values($errors)));
        }

        return $request;
    }

    /**
     * Помечает заявки как удаленные
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $request = $this->get($id);

        if (!$request->delete()) {
            $errors = $request->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении заявки' : implode("\n", array_values($errors)));
        }
    }

}
