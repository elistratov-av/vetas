<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.09.19
 * Time: 17:00
 */

namespace app\modules\admin\models\search;

use app\common\validators\PGIdValidator;
use app\modules\admin\data\AdminDataProvider;
use app\modules\soap\v2\models\db\ETPMessage as ETPMessageV2;
use yii\base\Model;

/**
 * MessageSearch represents the model behind the search form of `app\modules\admin\models\Message`.
 */
class MessageSearch extends ETPMessageV2
{
    public $visit_id;
    public $service_number;
    public $from;
    public $to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'visit_id'], 'trim'],
            [['id', 'visit_id'], PGIdValidator::class], // PSQL INTEGER	4 bytes
            [['service_number', 'phone', 'last_name', 'first_name', 'middle_name'], 'string'],
            [[
                'service_number', 'message', 'created_at', 'updated_at', 'phone',
                'last_name', 'first_name', 'middle_name', 'from', 'to',
            ], 'safe'],
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
        $query = ETPMessageV2::find()
            ->joinWith('visit')
            ->orderBy('created_at DESC')
        ;

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'message.id' => $this->id,
            'visit_id' => $this->visit_id,
        ]);

        $query->andFilterWhere(['ilike', 'service_number', $this->service_number])
            ->andFilterWhere(['ilike', 'message', $this->message])
            ->andFilterWhere(['ilike', 'phone', $this->phone])
            ->andFilterWhere(['ilike', 'last_name', $this->last_name])
            ->andFilterWhere(['ilike', 'first_name', $this->first_name])
            ->andFilterWhere(['ilike', 'middle_name', $this->middle_name]);

        if (!empty($this->from) && !empty($this->to)) {
            $query->andFilterWhere([
                'between',
                'message.created_at',
                $this->from,
                $this->to
            ]);
        }

        return $dataProvider;
    }
}
