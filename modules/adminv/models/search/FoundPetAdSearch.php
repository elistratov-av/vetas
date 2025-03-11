<?php

namespace app\modules\adminv\models\search;

use app\models\db\found_pet\Ad;
use app\modules\animalid\models\Model;
use yii\data\ActiveDataProvider;

/**
 * Class FoundPetAdSearch
 * @package app\modules\adminv\models\search
 */
class FoundPetAdSearch extends Model
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
     * @var bool
     */
    public $is_active;
    /**
     * @var string
     */
    public $created_at;
    /**
     * @var string
     */
    public $updated_at;
    /**
     * @var string
     */
    public $active_till;
    /**
     * @var string
     */
    public $closed_at;

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
        $query = Ad::find()
            ->with(
                'author',
                'address',
                'species',
                'breed',
                'color'
            );

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
                'is_active',
                'created_at',
                'updated_at',
                'active_till',
                'closed_at',
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

        return $dataProvider;
    }
}
