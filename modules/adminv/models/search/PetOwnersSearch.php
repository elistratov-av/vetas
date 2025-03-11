<?php

namespace app\modules\adminv\models\search;

use app\common\validators\PGIdValidator;
use app\models\db\PetOwners;
use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;

class PetOwnersSearch extends PetOwners
{
    public $id;
    public $fullname;
    public $birthday;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'trim'],
            [['id'], PGIdValidator::class], // PSQL INTEGER	4 bytes
            [['fullname', 'birthday'], 'safe'],
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
        $query = PetOwners::find()
            ->joinWith(['fias_addresses'], true)
            ->with(['fact_fias_addresses'])
            ->leftJoin('contacts', 'pet_owners.id = contacts.entity_id');
        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'fullname',
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
            'pet_owners.id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'fullname', $this->fullname]);
        if (!empty($this->birthday)) {
            $query->andWhere(new Expression("birthday::date = :birthday", [
                'birthday' => $this->birthday
            ]));
        }


        return $dataProvider;
    }

}
