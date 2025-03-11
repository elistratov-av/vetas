<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.11.18
 * Time: 17:28
 */

namespace app\modules\admin\models;

use yii\base\Model;
use app\modules\admin\data\AdminDataProvider;

/**
 * Class SpecSearch
 * @package app\modules\admin\models
 */
class SpecSearch extends Specialist
{
    public $login;
    public $short_name;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['fullname', 'short_name', 'reg_date', 'login'], 'safe'],
            [['reg_date'], 'date'],
        ];
    }

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
        $query = Specialist::find()->joinWith(['organization', 'user'], true);;

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'fullname',
                'login'
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

        $query->andFilterWhere(['ilike', 'fullname', $this->fullname])
            ->andFilterWhere(['ilike', 'login', $this->login])
            ->andFilterWhere(['ilike', 'short_name', $this->short_name])
            ->andFilterWhere(['=', 'reg_date', $this->reg_date]);

        return $dataProvider;
    }

}
