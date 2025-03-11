<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.11.18
 * Time: 17:28
 */

namespace app\modules\admin\models\search;

use app\models\db\Specialists;
use app\modules\admin\data\AdminDataProvider;

/**
 * Class SpecSearch
 * @package app\modules\admin\models
 */
class SpecialistSearch extends BaseSearchModel
{
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $fullname;
    /**
     * @var string
     */
    public $short_name;
    /**
     * @var string
     */
    public $reg_date;
    /**
     * @var string
     */
    public $expel_date;
    /**
     * @var string
     */
    public $birthday;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['fullname', 'short_name', 'login'], 'string'],
            [['reg_date', 'expel_date', 'birthday'], 'date'],
        ];
    }

    /**
     * @param $params
     * @return AdminDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params)
    {
        $query = Specialists::find()
            ->joinWith(['organization', 'user'], true);;

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

        $query->andFilterWhere(['ilike', 'fullname', $this->fullname])
            ->andFilterWhere(['ilike', 'login', $this->login])
            ->andFilterWhere(['ilike', 'short_name', $this->short_name])
            ->andFilterWhere(['=', 'reg_date', $this->reg_date])
            ->andFilterWhere(['=', 'reg_date', $this->reg_date])
            ->andFilterWhere(['=', 'expel_date', $this->expel_date])
            ->andFilterWhere(['=', 'birthday', $this->birthday]);

        return $dataProvider;
    }
}
