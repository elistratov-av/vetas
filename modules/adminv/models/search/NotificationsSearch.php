<?php


namespace app\modules\adminv\models\search;


use app\common\validators\PGIdValidator;
use app\models\db\subscription\SubscriptionLog;
use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;

class NotificationsSearch extends SubscriptionLog
{

    public function rules()
    {
        return [
            [['log_time'], 'date', 'format' => 'php:Y-m-d'],
            [['to'], 'string', 'max' => 255],
            [['to'], 'safe']
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params){
        $query = SubscriptionLog::find();
        $data_provider = new AdminDataProvider(
            ['query' => $query]
        );
        $data_provider->setSort([
            'attributes' => [
                'id',
                'log_time',
            ],
            'defaultOrder' => [
                'id'=>SORT_DESC
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $data_provider;
        }

        $query->andFilterWhere(['ilike', 'to', $this->to]);

        if (!empty($this->log_time)) {
            $query->andWhere(new Expression("log_time::date = :log_time", [
                'log_time' => $this->log_time
            ]));
        }

        return $data_provider;
    }
}