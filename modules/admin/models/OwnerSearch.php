<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.12.18
 * Time: 17:33
 */

namespace app\modules\admin\models;

use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;

class OwnerSearch extends Owners
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
        $query = Owners::find()->joinWith(['fiasAddress'], true)
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

        $query->andFilterWhere(['ilike', 'fullname', $this->fullname]);
        if (!empty($this->birthday)) {
            $query->andWhere(new Expression("birthday::date = :birthday", [
                'birthday' => $this->birthday
            ]));
        }


        return $dataProvider;
    }

}