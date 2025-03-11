<?php


namespace app\modules\v2\modules\tmc\models;


use app\models\db\tmc\ProductionForm;
use app\models\db\tmc\TmcBase;
use yii\web\BadRequestHttpException;

class ProductionFormsModel
{

    protected $allowed_types = [TmcBase::TYPE_DRUG, TmcBase::TYPE_VACCINE, TmcBase::TYPE_EXP_MATERIAL];

    /**
     * @param $type_tmc
     * @throws BadRequestHttpException
     */
    protected function checkTypeTmc($type_tmc)
    {
        if (!is_string($type_tmc) || !in_array($type_tmc, $this->allowed_types)) {
            throw new BadRequestHttpException('Формы выпуска доступны только для вакцин, препаратов и расходных материалов');
        }
    }

    /**
     * У некоторых существующих в БД ТМЦ не проставлены единицы измерения.
     * Принуждаем пользователя выставить их
     *
     * @param $id_tmc
     * @param $type_tmc
     * @return bool
     */
    protected function checkExistMeasure($id_tmc, $type_tmc)
    {
        $check = TmcBase::find()
            ->select('id_measure')

            ->where([
                'type' => $type_tmc,
                'id' => $id_tmc,
            ])
            ->scalar();
        return ($check !== null);
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     */
    public function getProductionForm($id)
    {
        $item = ProductionForm::find()
            ->select([
                'id', 'id_tmc', 'type_tmc',
                'name', 'volume', 'is_utilize'
            ])
            ->where([
                'AND',
                ['id' => $id],
                ['is_deleted' => false],
            ])
            ->one();

        if (empty($item)) {
            throw new BadRequestHttpException('Указанная форма выпуска не найдена');
        }

        return $item;
    }

    /**
     * @param $id_tmc
     * @param $type_tmc
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function getList($id_tmc, $type_tmc): array
    {
        $this->checkTypeTmc($type_tmc);

        if (!$this->checkExistMeasure($id_tmc, $type_tmc)){
            throw new BadRequestHttpException('У данного ТМЦ не указана единица измерения. ' .
            'Перед настройкой форм выпуска задайте единицу измерения на основной вкладке.');
        }

        return ProductionForm::find()
            ->select([
                'id', 'id_tmc', 'type_tmc',
                'name', 'volume', 'is_utilize'
            ])
            ->where([
                'AND',
                ['id_tmc' => $id_tmc],
                ['type_tmc' => $type_tmc],
                ['is_deleted' => false]
            ])
            ->asArray()
            ->all()
        ;
    }

    /**
     * @param $id_tmc
     * @param $type_tmc
     * @param $name
     * @param $volume
     * @param $is_utilize
     * @return ProductionForm
     * @throws BadRequestHttpException
     */
    public function create($id_tmc, $type_tmc, $name, $volume, $is_utilize)
    {
        $this->checkTypeTmc($type_tmc);
        if (!$this->checkExistMeasure($id_tmc, $type_tmc)){
            throw new BadRequestHttpException('У данного ТМЦ не указана единица измерения. ' .
                'Перед редактированием форм выпуска задайте единицу измерения на основной вкладке.');
        }

        $item= new ProductionForm([
            'id_tmc' => $id_tmc,
            'type_tmc' => $type_tmc,
            'name' => $name,
            'volume' => $volume,
            'is_utilize' => boolval($is_utilize),
        ]);

        if (!$item->save()) {
            $errors = $item->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании формы выпуска' : implode("\n", array_values($errors)));
        }

        return $item;
    }

    /**
     * @param $id
     * @param $name
     * @param $volume
     * @param $is_utilize
     * @return ProductionForm
     * @throws BadRequestHttpException
     */
    public function edit($id, $name, $volume, $is_utilize)
    {
        $item = ProductionForm::findOne([
            'id' => $id
        ]);

        if (empty($item)) {
            throw new BadRequestHttpException('Указанная формы выпуска не найдена');
        }

        if (!$this->checkExistMeasure($item->id_tmc, $item->type_tmc)){
            throw new BadRequestHttpException('У данного ТМЦ не указана единица измерения. ' .
                'Перед редактированием форм выпуска задайте единицу измерения на основной вкладке.');
        }

        $item->name = $name;
        $item->volume = $volume;
        $item->is_utilize = boolval($is_utilize);

        if (!$item->save()) {
            $errors = $item->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании формы выпуска ' : implode("\n", array_values($errors)));
        }

        return $item;
    }

    /**
     * @param $id
     * @return ProductionForm
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $item = ProductionForm::findOne([
            'id' => $id
        ]);

        if (empty($item)) {
            throw new BadRequestHttpException('Указанная формы выпуска не найдена');
        }

        $item->is_deleted = true;

        if (!$item->save()) {
            $errors = $item->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении формы выпуска ' : implode("\n", array_values($errors)));
        }
    }

}