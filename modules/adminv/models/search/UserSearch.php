<?php

namespace app\modules\adminv\models\search;

use app\common\models\UserModel;
use app\modules\admin\data\AdminDataProvider;
use yii\db\Expression;

/**
 * Class UserSearch
 * @package app\modules\adminv\models\search
 */
class UserSearch extends BaseSearchModel
{
    /**
     * @var int
     */
    public $id;
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
    public $login;
    /**
     * @var string
     */
    public $last_login;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['fullname', 'short_name', 'login'], 'string'],
            [['last_login'], 'date'],
        ];
    }

    /**
     * @param $params
     * @return AdminDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params)
    {
        $query = UserModel::find();
        $organizations_ids = $this->currentUserOrganizations();

        if (is_array($organizations_ids) && !empty($organizations_ids)) {
            $query->innerJoinWith(['specialists' => function ($q) use ($organizations_ids) {
                /* @var $q \yii\db\ActiveQuery*/
                $q->alias('specialist')
                    ->andWhere(['in', 'id_organization', $organizations_ids]);
            }]);
        } else {
            $query->joinWith(['specialists specialists', 'specialists.organization'], true);
        }

        $dataProvider = new AdminDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => ['id', 'fullname', 'login', 'last_login'],
            'defaultOrder' => [
                'login' => SORT_ASC,
            ],
        ]);

        if ($organizations_ids === false) {
            $dataProvider->query->emulateExecution();

            return $dataProvider;
        }

        $this->load($params);

        if (!$this->validate()) {
            $dataProvider->query->emulateExecution();

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
                'last_login' => $this->last_login,
            ]));
        }

        return $dataProvider;
    }
}
