<?php


namespace app\modules\v2\modules\tmc\models;


use app\common\validators\FullTrimValidator;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcEquipment;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;

class EquipmentsModel
{
    use TmcModelTrait;

    /**
     * @param int $id
     * @return array|ActiveRecord|null
     * @throws BadRequestHttpException
     */
    public function getEquipment(int $id)
    {
        $equipment = TmcEquipment::find()
            ->select([
                'id',
                'type',
                'name',
                'description',
                'is_deleted'
            ])
            ->where([
                'AND',
                ['id' => $id],
                ['tmc.tmc.type' => TmcBase::TYPE_EQUIPMENT],
                ['is_deleted' => false],
            ])
            ->asArray()
            ->one();

        if (empty($equipment)) {
            throw new BadRequestHttpException('Указанное оборудование не найдено');
        }

        return $equipment;
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

        $equipments = TmcEquipment::find()
            ->select([
                'id',
                'type',
                'name',
                'description',
                'is_deleted'
            ])
            ->asArray()
            ->andWhere([
                'AND',
                ['tmc.tmc.type' => TmcBase::TYPE_EQUIPMENT],
                ['is_deleted' => false],
            ])
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->orderBy('tmc.tmc.name ASC')
        ;

        if (!empty($filter)) {
            $this->applyFilter($equipments, $filter);
        }

        $count = clone $equipments;
        return new CommonList('equipments', $equipments->all(), $count->count(), $page, $limit);
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

        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'name', $filter['name']]);
        }

        if (!empty($filter['description'])) {
            $query->andWhere(['ILIKE', 'description', $filter['description']]);
        }
    }

    /**
     * @param $name
     * @param $description
     * @return TmcEquipment
     * @throws BadRequestHttpException
     */
    public function create($name, $description)
    {
        $equipment = new TmcEquipment();
        $equipment->type = TmcEquipment::TYPE_EQUIPMENT;
        $equipment->name = $name;
        $equipment->description = $description;

        if (!$equipment->save()) {
            $errors = $equipment->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании оборудования' : implode("\n", array_values($errors)));
        }

        return $equipment;
    }

    /**
     * @param $id
     * @param $name
     * @param $description
     * @return TmcEquipment
     * @throws BadRequestHttpException
     */
    public function edit($id, $name, $description)
    {
        $equipment = TmcEquipment::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_EQUIPMENT,
        ]);

        if (empty($equipment)) {
            throw new BadRequestHttpException('Указанное оборудование не найдено');
        }

        $equipment->name = $name;
        $equipment->description = $description;

        if (!$equipment->save()) {
            $errors = $equipment->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании оборудования' : implode("\n", array_values($errors)));
        }

        return $equipment;
    }

    /**
     * @param $id
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $equipment = TmcEquipment::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_EQUIPMENT,
        ]);

        if (empty($equipment)) {
            throw new BadRequestHttpException('Указанное оборудование не найдено');
        }

        $this->validateWhereIsBalance($equipment);

        $equipment->is_deleted = true;

        if (!$equipment->save()) {
            $errors = $equipment->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении оборудования' : implode("\n", array_values($errors)));
        }
    }

}
