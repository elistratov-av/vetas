<?php

namespace app\modules\adminfstek\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class BaseSearchModel
 * @package app\modules\adminfstek\models\search
 *
 * @property array $sortAttributes
 * @property array $defaultOrder
 */
abstract class BaseSearchModel extends Model
{
    /**
     * @param array $params
     * @return \yii\db\ActiveQuery
     */
    abstract protected function buildQuery($params = []);
    /**
     * @param \yii\db\ActiveQuery $query
     */
    abstract protected function buildFilter(&$query);

    /**
     * @param $params
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = $this->buildQuery($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->defaultPagesize(),
            ],
        ]);

        $sortParams = [];
        if (!empty($this->sortAttributes)) {
            $sortParams['attributes'] = $this->sortAttributes;
        }
        if (!empty($this->defaultOrder)) {
            $sortParams['defaultOrder'] = $this->defaultOrder;
        }
        if (!empty($sortParams)) {
            $dataProvider->setSort($sortParams);
        }

        $this->load($params);

        if (!$this->validate()) {
            $dataProvider->query->emulateExecution();

            return $dataProvider;
        }

        $this->buildFilter($dataProvider->query);

        return $dataProvider;
    }

    /**
     * @return int
     */
    protected function defaultPagesize()
    {
        return 20;
    }
}
