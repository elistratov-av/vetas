<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.11.18
 * Time: 13:42
 */

namespace app\modules\admin\models;

use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;

/**
 * Class UserSearch
 * @package app\modules\admin\models
 */
class UserSearch extends User
{
    public $fullname;
    public $short_name;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['fullname', 'short_name', 'last_login', 'login'], 'safe'],
            [['last_login'], 'date'],
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
        $query = User::find()->joinWith(['specialist specialist', 'specialist.organization'], true);;

        $dataProvider = new AdminDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => ['id', 'fullname', 'login', 'last_login'],
            'defaultOrder' => [
                'login' => SORT_ASC
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'fullname', $this->fullname])
            ->andFilterWhere(['ilike', 'login', $this->login])
            ->andFilterWhere(['ilike', 'short_name', $this->short_name]);

        if (!empty($this->last_login)) {
            $query->andWhere(new Expression("last_login::date = :last_login", [
                'last_login' => $this->last_login
            ]));
        }
        return $dataProvider;
    }

}
