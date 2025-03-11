<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.12.18
 * Time: 17:03
 */

namespace app\modules\admin\models;

use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;

class PetSearch extends Pets
{
    public $id;
    public $name;
    public $birthday;
    public $species;
    public $breeds;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'birthday', 'species', 'breeds'], 'safe'],
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
        $query = Pets::find()->joinWith(['species', 'breeds', 'owners'], true);

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'name',
                'birthday',
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

        $query->andFilterWhere(['ilike', 'pets.name', $this->name])
                ->andFilterWhere(['ilike', 'species.name', $this->species])
                ->andFilterWhere(['ilike', 'breeds.name', $this->breeds]);
        if (!empty($this->birthday)) {
            $query->andWhere(new Expression("pets.birthday::date = :birthday", [
                'birthday' => $this->birthday
            ]));
        }

        return $dataProvider;
    }

}
