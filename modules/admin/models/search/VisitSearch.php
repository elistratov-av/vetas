<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.12.18
 * Time: 17:38
 */

namespace app\modules\admin\models\search;


use app\common\validators\PGIdValidator;
use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\models\Visits;
use yii\base\Model;
use yii\db\Expression;

class VisitSearch extends Visits
{
    public $id;
    public $fullname;
    public $petName;
    public $short_name;
    public $created_at;
    public $status;
    public $time_range;
    public $from;
    public $to;
    public $ticket;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'trim'],
            [['id'], PGIdValidator::class], // PSQL INTEGER	4 bytes
            [['fullname', 'petName', 'short_name', 'created_at', 'status', 'from', 'to', 'time_range'], 'safe'],
            [['petName', 'ticket'], 'string'],
            [['created_at'], 'date'],
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
        $query = Visits::find()->joinWith(['organization', 'owner', 'pets', 'message'], true);

        $dataProvider = new AdminDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'fullname',
                'petName' => [
                    'asc' => ['pets.name' => SORT_ASC],
                    'desc' => ['pets.name' => SORT_DESC]
                ],
                'short_name',
                'created_at' => [
                    'asc' => ['visits.created_at' => SORT_ASC],
                    'desc' => ['visits.created_at' => SORT_DESC]
                ],
                'status',
                'time_range' => [
                    'asc' => ['visits.time_range' => new Expression('(case when channel = 4 then coalesce(fact_start_dttm, visits.created_at) else lower(time_range) end) asc')],
                    'desc' => ['visits.time_range' => new Expression('(case when channel = 4 then coalesce(fact_start_dttm, visits.created_at) else lower(time_range) end) desc')]
                ],
            ],
            'defaultOrder' => [
                'id' => SORT_DESC
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'visits.id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'organizations.short_name', $this->short_name])
            ->andFilterWhere(['ilike', 'fullname', $this->fullname])
            ->andFilterWhere(['in', 'status', $this->status])
            ->andFilterWhere(['ilike', 'pets.name', $this->petName])
            ->andFilterWhere(['ilike', 'visits.ticket_number', $this->ticket]);

        if (!empty($this->created_at)) {
            $query->andWhere(new Expression("visits.created_at::date = :createdAt", [
                'createdAt' => $this->created_at
            ]));
        }

        if (!empty($this->from) && !empty($this->to)) {
            $query->andFilterWhere([
                'between',
                new Expression('(case when channel = 4 then coalesce(fact_start_dttm, visits.created_at) else lower(time_range) end)::date'),
                $this->from,
                $this->to
            ]);
        }
        return $dataProvider;
    }

}
