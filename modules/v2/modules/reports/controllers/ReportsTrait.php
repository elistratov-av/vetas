<?php

namespace app\modules\v2\modules\reports\controllers;

use app\models\db\Reports;
use yii\web\BadRequestHttpException;

/**
 * Trait ReportsTrait
 * @package app\modules\v2\modules\reports\controllers
 *
 * @property string $type
 */
trait ReportsTrait
{
    /**
     * @param int $id
     * @return \app\models\db\Reports|null
     */
    protected function findModel(int $id)
    {
        return Reports::findOne([
            'id' => $id,
            'report_type' => $this->type,
        ]);
    }

    /**
     * @param array $condition
     * @param array $params
     * @return \app\models\db\Reports[]|array
     */
    protected function findModels($condition = [], $params = [])
    {
        $query = Reports::find()
            ->where(['report_type' => $this->type]);

        if (!empty($condition)) {
            $query->andWhere($condition);
        }
        if (isset($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        } else {
            $query->orderBy(['id' => SORT_ASC]);
        }
        if (isset($params['limit'])) {
            $query->limit($params['limit']);
        }
        if (isset($params['offset'])) {
            $query->offset($params['offset']);
        }
        if (isset($params['asArray'])) {
            $query->asArray($params['asArray']);
        }
        if (isset($params['with'])) {
            $query->with($params['with']);
        }

        return $query->all();
    }
}
