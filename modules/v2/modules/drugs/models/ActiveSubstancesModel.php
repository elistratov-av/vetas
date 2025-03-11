<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 08.10.20
 * Time: 14:26
 */

namespace app\modules\v2\modules\drugs\models;


use app\common\validators\FullTrimValidator;
use app\models\db\ActiveRecord;
use app\models\db\ActiveSubstances;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

class ActiveSubstancesModel
{
    /**
     * @param int $id
     * @return array|ActiveRecord|null
     * @throws BadRequestHttpException
     */
    public function getActiveSub(int $id)
    {
        $activeSub = ActiveSubstances::find()
            ->where(['id' => $id])
            ->asArray()
            ->one();

        if (empty($activeSub)) {
            throw new BadRequestHttpException('Указанное вещество не найдено');
        }

        return $activeSub;
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

        $activeSubs = ActiveSubstances::find()
            ->asArray()
            ->limit($limit)
            ->offset(($page - 1) * $limit)
        ;

        if (!empty($filter)) {
            $this->applyFilter($activeSubs, $filter);
        }

        $count = clone $activeSubs;
        $result = new CommonList('active_substances', $activeSubs->all(), $count->count('distinct active_substances.name'), $page, $limit);

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
            'name' => null, 'name_en' => null,
        ];

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['name', 'name_en'], 'string'],
            [['name', 'name_en'], FullTrimValidator::class],
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
        if (!empty($filter['name'])) {
            $query->andWhere([
                'or',
                ['ILIKE', 'active_substances.name', $filter['name']],
                ['ILIKE', 'active_substances.name_en', $filter['name']],
            ]);
        }
    }

    /**
     * @param string $name
     * @param $name_en
     * @param null $description
     * @return ActiveSubstances
     *
     * @throws BadRequestHttpException
     */
    public function create(
        $name,
        $name_en,
        $description = null
    )
    {
        $activeSub = new ActiveSubstances();
        $activeSub->name = $name;
        $activeSub->name_en = $name_en;
        $activeSub->description = $description;

        if (!$activeSub->save()) {
            $errors = $activeSub->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании вещества' : implode("\n", array_values($errors)));
        }

        return $activeSub;
    }

    /**
     * @param $id
     * @param string $name
     * @param $name_en
     * @param $description
     * @return \app\models\db\Drugs
     * @throws BadRequestHttpException
     */
    public function edit(
        $id,
        $name,
        $name_en,
        $description
    )
    {
        $activeSub = ActiveSubstances::findOne(['id' => $id]);

        if (empty($activeSub)) {
            throw new BadRequestHttpException('Указанное вещество не найдено');
        }

        $activeSub->name = $name;
        $activeSub->name_en = $name_en;
        $activeSub->description = $description;

        if (!$activeSub->save()) {
            $errors = $activeSub->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении препарата' : implode("\n", array_values($errors)));
        }

        return $activeSub;
    }

    /**
     * @param $id
     * @return ActiveSubstances|null
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $activeSub = ActiveSubstances::findOne(['id' => $id]);

        if (empty($activeSub)) {
            throw new BadRequestHttpException('Указанное вещество не найдено');
        }

        if (!$activeSub->delete()) {
            $errors = $activeSub->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении вещества' : implode("\n", array_values($errors)));
        }

        $activeSub->delete();

        return $activeSub;
    }

}