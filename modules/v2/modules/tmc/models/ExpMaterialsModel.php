<?php

namespace app\modules\v2\modules\tmc\models;

use app\common\validators\FullTrimValidator;
use app\models\db\tmc\CategoryToTmc;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcExpMaterial;
use app\modules\v2\common\skeletons\CommonList;
use Throwable;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;

class ExpMaterialsModel
{
    use TmcModelTrait;

    /**
     * @param int $id
     * @return array|ActiveRecord|null
     * @throws BadRequestHttpException
     */
    public function getExpMaterial(int $id)
    {
        $expMaterial = TmcExpMaterial::find()
            ->select([
                'tmc.tmc.id',
                'tmc.tmc.type',
                'tmc.tmc.name',
                'tmc.tmc.description',
                'id_measure',
                'tmc.tmc.is_uncountable',
            ])
            ->with([
                'measure' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'measures.id',
                        'measures.name',
                        'measures.description'
                    ]);
                },
                'categories'  => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'description'
                    ]);
                },
                'production_forms' => function ($query) {
                    /* @var $query ActiveQuery */
                    $query
                        ->select([
                            'id',
                            'id_tmc',
                            'type_tmc',
                            'name',
                            'volume',
                            'is_utilize',
                            'is_deleted',
                        ]);
                },
            ])
            ->where([
                'AND',
                ['id' => $id],
                ['is_deleted' => false],
                ['tmc.tmc.type' => TmcBase::TYPE_EXP_MATERIAL],
            ])
            ->asArray()
            ->one();

        if (empty($expMaterial)) {
            throw new BadRequestHttpException('Указанный расходный материал не найден');
        }

        return $expMaterial;
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

        $expMaterials = TmcExpMaterial::find()
            ->select([
                'tmc.tmc.id',
                'tmc.tmc.type',
                'tmc.tmc.name',
                'tmc.tmc.description',
                'tmc.tmc.is_uncountable',
                'id_measure',
            ])
            ->with([
                'measure' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'measures.id',
                        'measures.name',
                        'measures.description'
                    ]);
                },
                'categories'  => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'description'
                    ]);
                },
            ])
            ->andWhere([
                'AND',
                ['is_deleted' => false],
                ['tmc.tmc.type' => TmcBase::TYPE_EXP_MATERIAL],
            ])
            ->asArray()
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->orderBy('tmc.tmc.name ASC')
        ;

        if (!empty($filter)) {
            $this->applyFilter($expMaterials, $filter);
        }

        $count = clone $expMaterials;
        $result = new CommonList('exp-materials', $expMaterials->all(), $count->count(), $page, $limit);

        return $result;
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
            'name' => null, 'description' => null
        ];

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['description', 'name'], 'string', 'max' => 255],
            [['description', 'name'], FullTrimValidator::class],
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
        if (!empty($filter['id_measure'])) {
            $query->innerJoin('measures', 'measures.id = tmc.tmc.id_measure');
            $query->andWhere(['tmc.tmc.id_measure' => $filter['id_measure']]);
        }

        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'tmc.tmc.name', $filter['name']]);
        }

        if (!empty($filter['description'])) {
            $query->andWhere(['ILIKE', 'tmc.tmc.description', $filter['description']]);
        }
    }

    /**
     * @param $name
     * @param $id_measure
     * @param $description
     * @param int[] $category_ids
     * @param bool $is_uncountable
     * @return TmcExpMaterial
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function create($name, $id_measure, $description, $category_ids = [], $is_uncountable = false)
    {
        $expMaterial = new TmcExpMaterial();

        $expMaterial->type = TmcBase::TYPE_EXP_MATERIAL;
        $expMaterial->name = $name;
        $expMaterial->id_measure = $id_measure;
        $expMaterial->description = $description;
        $expMaterial->is_uncountable = $is_uncountable;

        return $this->save($expMaterial, $category_ids, 'Ошибка при создании расходного материала');
    }

    /**
     * @param $id
     * @param $name
     * @param $id_measure
     * @param $description
     * @param int[] $category_ids
     * @param bool $is_uncountable
     * @return TmcExpMaterial|null
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function edit($id, $name, $id_measure, $description, $category_ids = [], $is_uncountable = false)
    {
        $expMaterial = TmcExpMaterial::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_EXP_MATERIAL
        ]);

        if (empty($expMaterial)) {
            throw new BadRequestHttpException('Указанный расходный материал не найден');
        }

        $expMaterial->name = $name;
        $expMaterial->id_measure = $id_measure;
        $expMaterial->description = $description;
        $expMaterial->is_uncountable = $is_uncountable;

        return $this->save($expMaterial, $category_ids,  'Ошибка при редактировании расходного материала');
    }

    /**
     * @param $id
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $expMaterial = TmcExpMaterial::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_EXP_MATERIAL
        ]);
        if (empty($expMaterial)) {
            throw new BadRequestHttpException('Указанный расходный материал не найден');
        }

        $this->validateWhereIsBalance($expMaterial);

        $expMaterial->is_deleted = true;
        $this->save($expMaterial,[], 'Ошибка при удалении расходного материала');
    }

    /**
     *
     * @param $expMaterial
     * @param $category_ids
     * @param $error_msg
     * @return TmcExpMaterial
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    protected function save($expMaterial, $category_ids, $error_msg)
    {
        $transaction = TmcExpMaterial::getDb()->beginTransaction();

        if (!$expMaterial->save()) {
            $transaction->rollBack();
            $errors = $expMaterial->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? $error_msg : implode("\n", array_values($errors)));
        }

        // Привязываем категории
        try {
            CategoryToTmc::bathLinkCategoriesToTmc(
                $expMaterial->type,
                $expMaterial->id,
                $category_ids
            );

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $expMaterial;
    }
}
