<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\Species;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

class SpeciesModel
{
    /**
     * @return ActiveQuery
     * @throws \Throwable
     */
    public function list()
    {
        return Species::find()
            ->orderBy("name ASC");
    }

    /**
     * @param $id
     * @return Species
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $status = Species::findOne(['id' => $id]);

        if (empty($status)) {
            throw new BadRequestHttpException('Указанный вид не найден');
        }

        return $status;
    }

    /**
     * @param $name
     * @param $page
     * @param $limit
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search(
        $name, $page, $limit
    )
    {
        $query = Species::find();
        if (!is_null($name)) {
            $query = $query
                ->where(['ilike', 'name', $name]);
        }
        $query = $query
            ->orderBy('name ASC');
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'species', $query->all(),
            $count->count(), $page, $limit
        );
    }

}
