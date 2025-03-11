<?php

namespace app\modules\v2\modules\tmc\models;

use app\common\validators\FullTrimValidator;
use app\models\db\tmc\Category;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class CategoryModel
{
    /**
     * @param int $id
     * @return array|ActiveRecord|null
     * @throws BadRequestHttpException
     */
    public function getCategory(int $id)
    {
        $category = Category::find()
            ->select([
                'category.id',
                'category.name',
                'category.description',
                new Expression(
                    '
                    (
                    SELECT count(*) 
                    FROM tmc.category_to_tmc ctt 
                    WHERE ctt.id_category = category.id
                    ) AS count_tmc'
                ),
                new Expression(
                    '
                    (
                    SELECT count(*) 
                    FROM tmc.category_to_gov_services ctgs 
                    WHERE ctgs.id_category = category.id
                    ) AS count_gov_services'
                ),
            ])
            ->andWhere(['id' => $id])
            ->asArray()
            ->one();

        if (empty($category)) {
            throw new BadRequestHttpException('Указанная категория не найдена');
        }

        return $category;
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function list(int $page = 1, int $limit = 10, array $filter = [])
    {
        $filter = $this->validateFilter($filter);

        $categories = Category::find()
            ->select([
                'category.id',
                'category.name',
                'category.description',
                new Expression(
                    '
                    (
                    SELECT count(*) 
                    FROM tmc.category_to_tmc ctt 
                    WHERE ctt.id_category = category.id
                    ) AS count_tmc'
                ),
                new Expression(
                    '
                    (
                    SELECT count(*) 
                    FROM tmc.category_to_gov_services ctgs 
                    WHERE ctgs.id_category = category.id
                    ) AS count_gov_services'
                ),
            ])
            ->asArray()
            ->limit($limit)
            ->offset(($page - 1) * $limit)
        ;

        if (!empty($filter)) {
            $this->applyFilter($categories, $filter);
        }

        $count = clone $categories;
        return new CommonList('categories', $categories->all(), $count->count(), $page, $limit);
    }

    /**
     * @param $filter
     * @return array|void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    protected function validateFilter($filter)
    {
        if (empty($filter)) {
            return;
        }

        $empty_filter = [
            'name' => null,
            'description' => null,
            'id_service' => null,
            'tmc_name' => null,
        ];

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['id_service'], 'integer'],
            [['description', 'name', 'tmc_name'], 'string', 'max' => 255],
            [['description', 'name', 'tmc_name'], FullTrimValidator::class],
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }
        return $model->attributes;
    }

    /**
     * @param ActiveQuery $query
     * @param array $filter
     */
    protected function applyFilter(ActiveQuery $query, array $filter)
    {

        // NAME
        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'name', $filter['name']]);
        }

        // DESCRIPTION
        if (!empty($filter['description'])) {
            $query->andWhere(['ILIKE', 'description', $filter['description']]);
        }

        // ID_SERVICE
        if (!empty($filter['id_service'])){
            $query->innerJoin(
                'tmc.category_to_gov_services',
                'category_to_gov_services.id_category = category.id');
            $query->andWhere(['id_service' => $filter['id_service']]);
        }

        if (!empty($filter['tmc_name'])) {
            /*
            * джоин приводит к дублированию записей в выдаче,
            * потому - подзапрос
            */
            $sub_query = (new Query())
                ->from('tmc.category_to_tmc')
                ->select('id_category')
                ->innerJoin(
                    'tmc.tmc',
                    'tmc.category_to_tmc.id_tmc = tmc.tmc.id AND tmc.category_to_tmc.type_tmc = tmc.tmc.type')
                ->where([
                    'ILIKE', 'tmc.tmc.name', $filter['tmc_name']
                ]);

            $query->andWhere(['IN', 'tmc.category.id', $sub_query]);
        }
    }

    /**
     * @param $name
     * @param $description
     * @return Category
     * @throws BadRequestHttpException
     */
    public function create($name, $description)
    {
        $category = new Category();
        $category->name = $name;
        $category->description = $description;

        if (!$category->save()) {
            $errors = $category->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании категории' : implode("\n", array_values($errors)));
        }

        return $category;
    }

    /**
     * @param $id
     * @param $name
     * @param $description
     * @return Category
     * @throws BadRequestHttpException
     */
    public function edit($id, $name, $description)
    {
        $category = Category::findOne(['id' => $id]);

        if (empty($category)) {
            throw new BadRequestHttpException('Указанная категория не найдена');
        }

        $category->name = $name;
        $category->description = $description;

        if (!$category->save()) {
            $errors = $category->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании категории' : implode("\n", array_values($errors)));
        }

        return $category;
    }

    /**
     * @param $id
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $category = Category::findOne(['id' => $id]);

        if (empty($category)) {
            throw new BadRequestHttpException('Указанная категория не найдена');
        }

        if (!$category->delete()) {
            $errors = $category->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании категории' : implode("\n", array_values($errors)));
        }
    }

}
