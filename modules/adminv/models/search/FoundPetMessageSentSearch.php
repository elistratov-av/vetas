<?php

namespace app\modules\adminv\models\search;

use app\models\db\found_pet\MessageSent;
use app\modules\animalid\models\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class FoundPetMessageSentSearch
 * @package app\modules\adminv\models\search
 */
class FoundPetMessageSentSearch extends Model
{
    public  $id;
    public  $type;
    public  $service_number;
    public  $request;
    public  $response;
    public  $response_headers;
    public  $response_code;
    public  $user_error;
    public  $curl_error;
    public  $created_at;

    public $from;
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
        $query = MessageSent::find();

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
            ->andFilterWhere(['ilike', 'type', $this->type])
            ->andFilterWhere(['ilike', 'service_number', $this->service_number])
            ->andFilterWhere(['response_code' => $this->response_code])
        ;

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
