<?php

namespace app\modules\adminv\models\search;

use app\models\db\BalanceFlow;
use app\models\db\Users;
use app\models\db\V2VisitTmcBalance;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * BalanceFlowSearch represents the model behind the search form of `app\models\db\BalanceFlow`.
 */
class BalanceFlowSearch extends BalanceFlow
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_organization', 'id_balance_tmc_type', 'id_visitservice', 'count', 'created_by', 'updated_by'], 'integer'],
            [['balance_tmc_type', 'flow_type', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $id_organization
     *
     * @return ActiveDataProvider
     */
    public function search($params, $id_organization)
    {
        $query = self::find();
        $query
            ->select([
                'balance_tmc_type' => new Expression("
            CASE 
                WHEN balance_tmc_type='" . BalanceFlow::BALANCE_TMC_TYPE_DRUG . "' THEN  'balance_drugs'
                WHEN balance_tmc_type='" . BalanceFlow::BALANCE_TMC_TYPE_VACCINE . "' THEN  'balance_vaccines'
                WHEN balance_tmc_type='" . BalanceFlow::BALANCE_TMC_TYPE_EXP_MATERIAL . "' THEN  'balance_exp_materials'
                ELSE 'other'
            END "),
                'id',
                'id_organization',
                'id_balance_tmc_type',
                'id_visitservice',
                'flow_type',
                'count',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ])
            ->with('balanceItemInfo')
            ->with('userCreator')
            ->with('visitservice')
            ->orderBy('created_at DESC')
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'id_organization' => $id_organization,
            'id_balance_tmc_type' => $this->id_balance_tmc_type,
            'id_visitservice' => $this->id_visitservice,
            'count' => $this->count,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['ilike', 'balance_tmc_type', $this->balance_tmc_type])
            ->andFilterWhere(['ilike', 'flow_type', $this->flow_type]);

        return $dataProvider;
    }

    /**
     * Дополнительная связь на view со списком балансовых препаратов/расходников
     * @return \yii\db\ActiveQuery
     */
    public function getBalanceItemInfo()
    {
        return $this->hasOne(V2VisitTmcBalance::class, [
            'id_organization' => 'id_organization',
            'balance_type' => 'balance_tmc_type',   // Тут они не совпадают потому придется использовать case в запросе
            'id' => 'id_balance_tmc_type'
        ]);
    }

    /**
     * Дополнительная связь на пользователя
     * @return \yii\db\ActiveQuery
     */
    public function getUserCreator()
    {
        return $this->hasOne(Users::class,[
            'id' => 'created_by'
        ]);
    }
}
