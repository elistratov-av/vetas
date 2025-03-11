<?php

namespace app\modules\adminv\models\search;

use app\models\db\found_pet\Message;
use app\modules\animalid\models\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class FoundPetMessageSearch
 * @package app\modules\adminv\models\search
 */
class FoundPetMessageSearch extends Model
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $service_number;
    /**
     * @var array
     */
    public $body;
    /**
     * @var array
     */
    public $headers;
    /**
     * @var string
     */
    public $created_at;
    /**
     * @var string
     */
    public $from;
    /**
     * @var string
     */
    public $to;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [$this->attributes(), 'safe'],
        ];
    }

    /**
     * @param array $params
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params)
    {
        $query = Message::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'type',
                'service_number',
                'created_at',
            ],
            'defaultOrder' => [
                'id' => SORT_DESC,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['=', 'type', $this->type])
            ->andFilterWhere(['ilike', 'service_number', $this->service_number]);

        // if (!empty($this->created_at)) {
        //     $query->andWhere(new Expression('created_at::date = :created_at', ['created_at' => $this->created_at]));
        // }
        if (!empty($this->from)) {
            $query->andWhere(new Expression('created_at::date >= :from', ['from' => $this->from]));
        }
        if (!empty($this->to)) {
            $query->andWhere(new Expression('created_at::date <= :to', ['to' => $this->to]));
        }

        return $dataProvider;
    }
}
