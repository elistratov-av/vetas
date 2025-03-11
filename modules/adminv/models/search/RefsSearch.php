<?php

namespace app\modules\adminv\models\search;

use app\models\db\PetOwnerType;
use app\modules\admin\data\AdminDataProvider;
use yii\db\Expression;
use yii\db\Query;

class RefsSearch extends BaseSearchModel
{
    /**
     * @var string
     */
    public $title;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['title', ], 'string'],
        ];
    }

    /**
     * @param array $params
     * @param bool  $isTech
     * @return AdminDataProvider
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params, $className)
    {
        $this->load($params);
        $query = $className::find();

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'title',
            ],
            'defaultOrder' => [
                'id' => SORT_ASC
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            $className::tablename() . '.title' => $this->title,
        ]);

        return $dataProvider;
    }
}
