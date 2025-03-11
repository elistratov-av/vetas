<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\Pets;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class AnimalModel
{
    /**
     * @param $id_owner
     * @return Query
     * @throws \Throwable
     */
    public function list($id_owner)
    {
        return $this->selectQuery()
            ->where(['pto.id_owner' => $id_owner])
            ->orderBy("p.name");
    }

    /**
     * @param $id
     * @return array
     */
    public function get($id)
    {
        return $this->selectQuery()
            ->where(['p.id' => $id])
            ->one();
    }

    /**
     * @param $id_owner
     * @param $nickname
     * @param $type
     * @param $breed
     * @param $gender_male
     * @param $processing
     * @param $page
     * @param $limit
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search(
        $id_owner, $nickname, $type,
        $breed, $gender_male, $processing,
        $page, $limit
    )
    {
        $filter = ['and'];
        if (!is_null($nickname)) {
            $filter[] = ['ilike', 'p.name', $nickname];
        }
        if (!is_null($id_owner)) {
            $filter[] = ['pto.id_owner' => $id_owner];
        }
        if (!is_null($type)) {
            $filter[] = ['s.name' => $type];
        }
        if (!is_null($breed)) {
            $filter[] = ['ilike', 'b.name', $breed];
        }
        if (!is_null($gender_male)) {
            if ($gender_male) {
                $filter[] = ['p.sex' => 'm'];
            } else {
                $filter[] = ['p.sex' => 'f'];
            }
        }
        if (!is_null($processing)) {
            $filter[] = ['ilike', 'pha.processing', $processing];
        }
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $query = $this->selectQuery()
            ->andFilterWhere($filter)
            ->orderBy('p.name ASC');
        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'pet_hotel_animal', $query->all(),
            $count->count(), $page, $limit
        );

    }

    /**
     * @param $id_pet
     * @param $processing
     * @throws Exception
     */
    public function create(
        $id_pet, $processing
    )
    {
        if (!is_null($processing)) {
            \Yii::$app->db->createCommand()
                ->insert('public.pet_hotel_animal', [
                    'id_pet' => $id_pet,
                    'processing' => $processing,
                ])
                ->execute();
        }
    }

    /**
     * @param $id_pet
     * @param $processing
     * @throws Exception
     */
    public function edit(
        $id_pet, $processing = null
    )
    {
        if (!is_null($processing)) {
            \Yii::$app->db->createCommand()
                ->update(
                    'public.pet_hotel_animal',
                    ['processing' => $processing],
                    ['id_pet' => $id_pet]
                )
                ->execute();
        }
    }

    /**
     * @param $id_pet
     * @throws Exception
     */
    public function delete($id_pet)
    {
        \Yii::$app->db->createCommand()
            ->delete(
                'public.pet_hotel_animal',
                ['id_pet' => $id_pet]
            )
            ->execute();
    }

    public function selectQuery(): Query
    {
        $query = new Query();
        return $query
            ->select([
                'p.id', 'p.name as nickname',
                'pto.id_owner', 'po.fullname as fio_owner',
                's.id as id_type', 's.name as type',
                'b.id as id_breed', 'b.name as breed',
                new Expression('p.sex=\'m\' as gender_male'),
                'pha.processing'
            ])
            ->from(Pets::tableName() . ' p')
            ->leftJoin('pet_hotel_animal pha', 'pha.id_pet=p.id')
            ->leftJoin('pets_to_owner pto', 'pto.id_pet=p.id')
            ->leftJoin('pet_owners po', 'po.id=pto.id_owner')
            ->leftJoin('breeds b', 'b.id=p.id_breed')
            ->leftJoin('species s', 's.id=b.species_id');
    }

}
