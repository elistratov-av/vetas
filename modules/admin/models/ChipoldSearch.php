<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.01.19
 * Time: 17:12
 */

namespace app\modules\admin\models;


use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;

/**
 * Class ChipoldSearch
 * @package app\modules\admin\models
 */
class ChipoldSearch extends PetsOldIdentification
{
    public $id;
    public $pet;
    public $id_pet;
    public $identification_code;
    public $birthday;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['pet', 'id_pet', 'identification_code'], 'safe'],
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
        $query = PetsOldIdentification::find()->joinWith(['pet', 'identType'], true)->where(['moved' => false]);

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'id_pet',
            ],
            'defaultOrder'=>[
                'id'=>SORT_ASC
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'pets.name', $this->pet])
            ->andFilterWhere(['=', 'id_pet', $this->id_pet])
            ->andFilterWhere(['ilike', 'identification_code', $this->identification_code]);

        return $dataProvider;
    }

}