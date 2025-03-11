<?php

namespace app\modules\adminv\models\search;

use app\models\db\PetOwnerType;
use app\models\db\Pets;
use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use app\common\validators\PGIdValidator;

class PetSearch extends Pets
{
    public $id;
    public $name;
    public $birthday;
    public $species;
    public $breeds;
    public $fullname;
    public $identification_code;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'trim'],
            [['id'], PGIdValidator::class], // PSQL INTEGER	4 bytes
            [['fullname'], 'string', 'max' => 255],
            [['identification_code'], 'string', 'max' => 50],
            [['name', 'birthday', 'species', 'breeds', 'fullname'], 'safe'],
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
        $query = Pets::find()
            ->joinWith([
                'species',
                'breeds',
                'pet_identification',
            ], true);

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'name',
                'birthday',
            ],
            'defaultOrder' => [
                'id' => SORT_ASC
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'pets.id' => $this->id,
        ]);

        $query
            ->andFilterWhere(['ilike', 'pets.name', $this->name])
            ->andFilterWhere(['ilike', 'species.name', $this->species])
            ->andFilterWhere(['ilike', 'breeds.name', $this->breeds])
            ->andFilterWhere(['ilike', 'pet_identification.identification_code', $this->identification_code]);

        if (!empty($this->birthday)) {
            $query->andWhere(new Expression("pets.birthday::date = :birthday", [
                'birthday' => $this->birthday
            ]));
        }

        if (!empty($this->fullname)) {
            $query
                ->leftJoin('pets_to_owner', 'pets_to_owner.id_pet = pets.id')
                ->leftJoin('pet_owner_type', 'pet_owner_type.id = pets_to_owner.id_owner_type')
                ->leftJoin('pet_owners', 'pets_to_owner.id_owner = pet_owners.id')
                ->where([
                    'AND',
                    ['pets_to_owner.id_owner_type' => (new Query())
                        ->select('id')
                        ->from(PetOwnerType::tableName())
                        ->where(['is_owner' => true])
                        ->column()],
                    ['ilike', 'pet_owners.fullname', $this->fullname],
                ]);
        }

        return $dataProvider;
    }
}
