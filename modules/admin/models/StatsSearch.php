<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.12.18
 * Time: 16:49
 */

namespace app\modules\admin\models;

use app\modules\admin\data\AdminDataProvider;
use yii\db\Expression;

class StatsSearch extends StatisticMosRu
{
    public $short_name;
    public $created_at;
    public $stat_filter;
    public $status;
    public $c;

    /**
     * @param $params
     * @return AdminDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params)
    {
        $query = StatisticMosRu::find();

        $dataProvider = new AdminDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $dataProvider->setSort(
            [
                'attributes' => ['short_name', 'created_at', 'status', 'c']
            ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if (!empty($this->created_at)) {
            $query->andWhere(new Expression("created_at::date = :created_at", [
                'created_at' => $this->created_at
            ]));
        }
        return $dataProvider;
    }
}
