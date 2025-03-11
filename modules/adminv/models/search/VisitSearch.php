<?php

namespace app\modules\adminv\models\search;

use app\common\validators\PGIdValidator;
use app\models\db\Visits;
use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;

class VisitSearch extends BaseSearchModel
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
    public $fias_addresses;
    public $fact_fias_addresses;
    public $ticket;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'trim'],
            [['id'], PGIdValidator::class], // PSQL INTEGER	4 bytes
            [
                ['fullname', 'petName', 'short_name', 'created_at', 'status', 'from', 'to', 'time_range', 'fact_fias_addresses', 'fias_addresses'],
                'safe'
            ],
            ['petName', 'string'],
            /*
             * "1" => "Указан", "2" => "Не указан"
             */
            [['fact_fias_addresses', 'fias_addresses'], 'integer'],
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
     * @return \yii\db\ActiveQuery
     */
    protected function getQuery()
    {
        return Visits::find()
            ->joinWith([
                'organization',
                'owner',
                'pets',
                'owner.fact_fias_addresses AS fact_fias_addresses',
                'owner.fias_addresses AS fias_addresses'
            ], true);
    }

    /**
     * @param $params
     * @return AdminDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params)
    {
        $query = $this->getQuery();

        $dataProvider = $this->getDataProvider();
        $dataProvider->query = $query;

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // Основные
        $query
            ->andFilterWhere(['visits.id' => $this->id])
            ->andFilterWhere(['ilike', 'organizations.short_name', $this->short_name])
            ->andFilterWhere(['ilike', 'fullname', $this->fullname])
            ->andFilterWhere(['in', 'status', $this->status])
            ->andFilterWhere(['ilike', 'visits.ticket_number', $this->ticket])
            ->andFilterWhere(['ilike', 'pets.name', $this->petName]);


        // Создан
        if (!empty($this->created_at)) {
            $query->andWhere(new Expression("visits.created_at::date = :createdAt", [
                'createdAt' => $this->created_at
            ]));
        }

        // Время приема
        if (!empty($this->from) && !empty($this->to)) {
            $query->andFilterWhere([
                'between',
                new Expression('(case when channel = 4 then coalesce(fact_start_dttm, visits.created_at) else lower(time_range) end)::date'),
                $this->from,
                $this->to
            ]);
        }

        // факт адрес
        if ($this->fact_fias_addresses == 1) {
            $query->andWhere(['NOT', ['pet_owners.id_fact_fias_address' => null]]);
        } elseif ($this->fact_fias_addresses == 2) {
            $query->andWhere(['pet_owners.id_fact_fias_address' => null]);
        }

        // адрес
        if ($this->fias_addresses == 1) {
            $query->andWhere(['NOT', ['pet_owners.id_fias_address' => null]]);
        } elseif ($this->fias_addresses == 2) {
            $query->andWhere(['pet_owners.id_fias_address' => null]);
        }

        return $dataProvider;
    }

    /**
     * @return AdminDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    protected function getDataProvider()
    {
        $dataProvider = new AdminDataProvider([
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
                'fact_fias_addresses' => [
                    'asc' => ['fact_fias_addresses.full_address' => SORT_ASC],
                    'desc' => ['fact_fias_addresses.full_address' => SORT_DESC]
                ],
                'fias_addresses' => [
                    'asc' => ['fias_addresses.full_address' => SORT_ASC],
                    'desc' => ['fias_addresses.full_address' => SORT_DESC]
                ],
            ],
            'defaultOrder' => [
                'id' => SORT_DESC
            ]
        ]);

        return $dataProvider;
    }
}
