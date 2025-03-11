<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.12.18
 * Time: 14:46
 */

namespace app\modules\admin\models;

use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;

/**
 * Class AddressSearch
 * @package app\modules\admin\models
 */
class OrgSearch extends Organization
{
    public $id;
    public $short_name;
    public $parent_id;
    public $addresses;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['short_name', 'parent_id', 'addresses'], 'safe'],
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
        $query = Organization::find()->joinWith(['parentOrg', 'contacts', 'addresses'],  true);

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'short_name' => [
                    'asc' => ['short_name' => SORT_ASC],
                    'desc' => ['short_name' => SORT_DESC],
                ],
                'parent_id',
                'addresses',
            ],
            'defaultOrder'=>[
                'short_name'=>SORT_ASC
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'organizations.short_name', $this->short_name])
            ->andFilterWhere(['ilike', 'parent.short_name', $this->parent_id])
            ->andFilterWhere(['ilike', 'addresses.name', $this->addresses]);

        return $dataProvider;
    }

}