<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 06.12.18
 * Time: 17:18
 */

namespace app\modules\admin\models\search;


use app\common\validators\PGIdValidator;
use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\models\StatusLog;
use yii\base\Model;
use yii\db\Expression;

/**
 * Class LogSearch
 * @package app\modules\admin\models
 */
class LogSearch extends StatusLog
{
    public $id;
    public $log_time;
    public $service_number;
    public $etp_status;
    public $visit_id;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['visit_id'], 'trim'],
            [['visit_id'], PGIdValidator::class], // PSQL INTEGER	4 bytes
            [['visit_id'], 'integer'],
            [['log_time', 'service_number', 'etp_status'], 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param $params
     * @return AdminDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params)
    {
        $query = StatusLog::find();

        $dataProvider = new AdminDataProvider([
            'query' => $query,
            'pagination' =>[
                'pageSize' => 50,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'log_time',
                'service_number',
                'etp_status',
                'visit_id',
            ],
            'defaultOrder'=>[
                'id'=>SORT_DESC
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);
        $query->andFilterWhere(['ilike', 'service_number', $this->service_number])
            ->andFilterWhere(['ilike', 'etp_status', $this->etp_status])
            ->andFilterWhere(['visit_id' => $this->visit_id]);

        if (!empty($this->log_time)) {
            $query->andWhere(new Expression("log_time::date = :log_time", [
                'log_time' => $this->log_time
            ]));
        }

        return $dataProvider;
    }

}
