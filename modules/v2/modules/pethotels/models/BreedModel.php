<?php

namespace app\modules\v2\modules\pethotels\models;

use app\models\db\Breeds;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

class BreedModel
{
    /**
     * @return ActiveQuery
     * @throws \Throwable
     */
    public function list($id_species)
    {
        return Breeds::find()
            ->where(['species_id' => $id_species])
            ->orderBy("name ASC");
    }

    /**
     * @param $id
     * @return Breeds
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $breed = Breeds::findOne(['id' => $id]);

        if (empty($breed)) {
            throw new BadRequestHttpException('Указанная порода не найдена');
        }

        return $breed;
    }

    /**
     * @param $id_species
     * @param $name
     * @param $page
     * @param $limit
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search(
        $id_species = null, $name = null,
        $page = null, $limit = null
    )
    {
        $filter = ['and'];
        if (!is_null($id_species)) {
            $filter[] = ['species_id' => $id_species];
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
        $query = Breeds::find()
            ->andFilterWhere($filter)
            ->orderBy('name ASC');
        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'breeds', $query->all(),
            $count->count(), $page, $limit
        );
    }

    /**
     * @param $name
     * @return Breeds
     * @throws BadRequestHttpException
     */
    public function create(
        $id_species, $name
    )
    {
        $breed = new Breeds();

        $breed->species_id = $id_species;
        $breed->name = $name;

        if (!$breed->save()) {
            $errors = $breed->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании породы' : implode("\n", array_values($errors)));
        }

        return $breed;
    }

    /**
     * @param $id
     * @param $id_species
     * @param $name
     * @return Breeds
     * @throws BadRequestHttpException
     */
    public function edit(
        $id, $id_species = null, $name = null
    )
    {
        $breed = $this->get($id);

        $breed->species_id = $id_species ?? $breed->species_id;
        $breed->name = $name ?? $breed->name;

        if (!$breed->save()) {
            $errors = $breed->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании породы' : implode("\n", array_values($errors)));
        }

        return $breed;
    }

}
