<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.01.19
 * Time: 13:11
 */

namespace app\modules\admin\models;


use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;

/**
 * Class ChipsSearch
 * @package app\modules\admin\models
 */
class ChipsSearch extends PetIdentification
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
        $query = PetIdentification::find()->joinWith(['pet', 'ident_type'], true);

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