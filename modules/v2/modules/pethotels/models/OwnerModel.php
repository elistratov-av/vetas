<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\Contacts;
use app\models\db\PetOwners;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class OwnerModel
{

    /**
     * @param $id
     * @return array
     */
    public function get($id)
    {
        return $this
            ->selectQuery()
            ->andWhere(['po.id' => $id])
            ->one();
    }

    /**
     * @param $i_fio
     * @param $o_fio
     * @param $f_fio
     * @param $phone_number
     * @param $page
     * @param $limit
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search(
        $i_fio, $o_fio, $f_fio,
        $fio, $phone_number, $address,
        $page, $limit
    )
    {
        $filter = ['and'];
        if (!is_null($fio)) {
            $filter[] = ['ilike', 'po.fullname', $fio];
        } else {
            if (!is_null($i_fio)) {
                $filter[] = ['ilike', 'po.i_fio', $i_fio];
            }
            if (!is_null($o_fio)) {
                $filter[] = ['ilike', 'po.o_fio', $o_fio];
            }
            if (!is_null($f_fio)) {
                $filter[] = ['ilike', 'po.f_fio', $f_fio];
            }
        }
        if (!is_null($phone_number)) {
            $phone_number_2 = preg_replace("/[^0-9]/", "", $phone_number);
            $filter[] = ['ilike', 'сo.name', $phone_number_2];
        }
        if (!is_null($address)) {
            $filter[] = ['ilike', 'phoa.address', $address];
        }
        $filter[] = ['or', ['ct.type' => null], ['ct.type' => 'phone']];
        $filter[] = ['or', ['ct.entity_type' => null], ['ct.entity_type' => 'pet_owner']];

        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $query = $this
            ->selectQuery()
            ->andWhere($filter)
            ->orderBy('po.fullname');

        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'pet_hotel_animal_owner', $query->all(),
            $count->count(), $page, $limit
        );

    }

    /**
     * Создает владельца
     *
     * @param $id_owner
     * @param $phone_number
     * @param $address
     * @throws Exception
     */
    public function create(
        $id_owner, $phone_number, $address
    )
    {
        if (!is_null($phone_number) || !is_null($address)) {
            $columns = ['id_owner' => $id_owner];
            if (!is_null($phone_number)) {
                $columns['phone_number'] = preg_replace("/[^0-9]/", "", $phone_number);
            }
            if (!is_null($address)) {
                $columns['address'] = $address;
            }
            \Yii::$app->db->createCommand()
                ->insert('public.pet_hotel_animal_owner', $columns)
                ->execute();
        }
    }

    /**
     * @param $id
     * @param $phone_number
     * @param $address
     * @throws Exception
     */
    public function edit(
        $id_owner, $phone_number = null, $address = null
    )
    {
        if (!is_null($phone_number) || !is_null($address)) {
            $columns = [];
            if (!is_null($phone_number)) {
                $columns['phone_number'] = preg_replace("/[^0-9]/", "", $phone_number);
            }
            if (!is_null($address)) {
                $columns['address'] = $address;
            }
            \Yii::$app->db->createCommand()
                ->update(
                    'public.pet_hotel_animal_owner',
                    $columns, ['id_owner' => $id_owner]
                )
                ->execute();
        }
    }

    /**
     * Помечает владельца как удаленную
     *
     * @param $id_owner
     * @throws Exception
     */
    public function delete($id_owner)
    {
        \Yii::$app->db->createCommand()
            ->delete(
                'public.pet_hotel_animal_owner',
                ['id_owner' => $id_owner]
            )
            ->execute();
    }

    public function selectQuery(): Query
    {
        $query = new Query();
        return $query
            ->select([
                'po.id', 'po.i_fio', 'po.o_fio', 'po.f_fio',
                'po.fullname',
                new Expression('coalesce(phao.address, cast(fias.id as TEXT)) as address'),
                new Expression('coalesce(phao.phone_number, сo.name) as phone_number'),
                '(select full_address from fias_addresses fa where fa.id = coalesce(cast (phao.address as int), fias.id)) as address_str',
            ])
            ->from(PetOwners::tableName() . ' po')
            ->leftJoin('public.pet_hotel_animal_owner phao', 'phao.id_owner=po.id')
            ->leftJoin('public.contacts сo', 'сo.entity_id=po.id')
            ->leftJoin('public.contact_types ct', 'сo.id_contact_type=ct.id')
            ->leftJoin('public.fias_addresses fias', 'po.id_fias_address=fias.id')
            ->andWhere(['and',
                ['or', ['ct.type' => null], ['ct.type' => 'phone']],
                ['or', ['ct.entity_type' => null], ['ct.entity_type' => 'pet_owner']],
            ]);
    }

}
