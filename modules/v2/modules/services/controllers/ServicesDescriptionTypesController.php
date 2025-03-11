<?php

namespace app\modules\v2\modules\services\controllers;

use app\models\db\DescriptionTypes;
use app\models\db\ServicesDescriptionTypes;
use app\modules\v2\modules\BaseController;
use yii\base\DynamicModel;
use yii\web\BadRequestHttpException;

class ServicesDescriptionTypesController extends BaseController
{
    /**
     * @param $id_service
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGet($id_service)
    {
        $result = ServicesDescriptionTypes::find()
            ->select([
                ServicesDescriptionTypes::tableName() . '.id',
                ServicesDescriptionTypes::tableName() . '.required',
                DescriptionTypes::tableName() . '.name',
                DescriptionTypes::tableName() . '.tech_name',
            ])
            ->where([
                'id_service' => $id_service
            ])
            ->leftJoin(
                DescriptionTypes::tableName(),
                DescriptionTypes::tableName() . '.id = ' . ServicesDescriptionTypes::tableName() . '.id_description_type'
            )
            ->asArray()
            ->all();

        return [
            'result' => $result
        ];
    }

    /**
     * @param $id_service
     * @param $description_types
     * @return bool[]
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionSave($id_service, $description_types)
    {
        $description_types_new = $this->validateDescriptionTypesArray($description_types);
        $description_types_in_db = ServicesDescriptionTypes::find()
            ->where([
                'id_service' => $id_service
            ])
            ->indexBy('id')
            ->all();

        ServicesDescriptionTypes::getDb()->beginTransaction();

        foreach ($description_types_new as $key => $new_value) {
            if (!array_key_exists($key, $description_types_in_db)) {
                throw new BadRequestHttpException('Ошибка: элемента с ID ' . $key . ' нет в списке');
            }

            /** @var $model ServicesDescriptionTypes */
            $model = $description_types_in_db[$key];
            $model->required = $new_value->required;
            $model->save();

            if ($model->hasErrors()) {
                $errors = $model->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
            }
        }

        ServicesDescriptionTypes::getDb()->transaction->commit();

        return [
            'result' => true,
        ];
    }

    /**
     * @param $description_types
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateDescriptionTypesArray($description_types)
    {
        $result = null;

        if (!is_array($description_types)) {
            throw new BadRequestHttpException('description_types должен быть массивом');
        }

        foreach ($description_types as $description_type) {
            $empty_filter = [
                'id' => null, 'required' => null,
            ];

            $filter = array_merge($empty_filter, $description_type);

            $rules = [
                [['id', 'required'], 'required'],
                [['id'], 'integer'],
                [['required'], 'boolean'],
            ];

            $model = DynamicModel::validateData($filter, $rules);

            if ($model->hasErrors()) {
                $errors = $model->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
            }

            $result[$model->id] = $model;
        }

        return $result;
    }
}