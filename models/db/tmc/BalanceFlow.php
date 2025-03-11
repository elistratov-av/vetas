<?php

namespace app\models\db\tmc;

use app\models\db\ActiveRecord;
use app\models\db\VisitsGovServices;
use Yii;

/**
 * This is the model class for table "tmc.balance_flow".
 *
 * @property int $id
 * @property int $id_tmc_balance Ссылка на балансовую единицу
 * @property string $flow_type Тип операции (приход/расход/холд)
 * @property string $flow_action Действие
 * @property int $count Кол-во
 * @property int $id_visit_service Ссылка на данные приема
 * @property int $id_balance_action Ссылка на действия с ТМЦ
 * @property int $created_by Автор добавления
 * @property string $created_at Дата создания
 *
 * @property BalanceAction $balanceAction Действие с ТМЦ
 * @property Balance $balance Баланс ТМЦ
 */
class BalanceFlow extends ActiveRecord
{
    /**
     * Увеличение значения баланса
     */
    const FLOW_TYPE_INCREASE = 'I';

    /**
     * Уменьшение значения баланса
     */
    const FLOW_TYPE_DECREASE = 'D';

    /**
     * "Заморозка" указанного кол-ва (происходит уменьшение доступного)
     */
    const FLOW_TYPE_HOLD = 'H';

    /**
     * Действие: расход (в приеме). Исторически сложившийся тип
     */
    const FLOW_ACTION_EXPENSE = 'expense';

    /**
     * Действие: списание остатков в приеме. ТОЛЬКО В ПРИЕМЕ!
     */
    const FLOW_ACTION_EXPENSE_UTILIZE_IN_VISIT = 'expense_utilize';

    /**
     * Действие: пополнение баланса (для организации). Исторически сложившийся тип
     */
    const FLOW_ACTION_INCOME_TO_ORG = 'income';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.balance_flow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_tmc_balance', 'flow_type', 'flow_action', 'count'], 'required'],
            [['id_tmc_balance', 'id_visit_service', 'id_balance_action', 'created_by'], 'default', 'value' => null],
            [['id_tmc_balance', 'id_visit_service', 'id_balance_action', 'created_by'], 'integer'],
            [['count'], 'number'],
            [['count'], 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['created_at'], 'safe'],
            [['flow_type'], 'string', 'max' => 1],
            [
                ['flow_type'], 'in',
                'range' => [
                    BalanceFlow::FLOW_TYPE_DECREASE,
                    BalanceFlow::FLOW_TYPE_INCREASE,
                    BalanceFlow::FLOW_TYPE_HOLD,
                ],
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['flow_action'], 'string', 'max' => 16],
            [
                ['flow_action'], 'in',
                'range' => [
                    BalanceFlow::FLOW_ACTION_EXPENSE,
                    BalanceFlow::FLOW_ACTION_INCOME_TO_ORG,
                    BalanceFlow::FLOW_ACTION_EXPENSE_UTILIZE_IN_VISIT,
                ],
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['id_visit_service'], 'exist', 'skipOnError' => true, 'targetClass' => VisitsGovServices::class, 'targetAttribute' => ['id_visit_service' => 'id']],
            [['id_tmc_balance'], 'exist', 'skipOnError' => true, 'targetClass' => Balance::class, 'targetAttribute' => ['id_tmc_balance' => 'id']],
            /** @todo  BalanceAction */
           // [['id_balance_action'], 'exist', 'skipOnError' => true, 'targetClass' => BalanceAction::class, 'targetAttribute' => ['id_balance_action' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_tmc_balance' => 'Ссылка на балансовую единицу',
            'flow_type' => 'Тип операции (приход/расход/холд)',
            'flow_action' => 'Действие',
            'count' => 'Кол-во',
            'id_visit_service' => 'Ссылка на данные приема',
            'id_balance_action' => 'Ссылка на действия с ТМЦ',
            'created_by' => 'Автор добавления',
            'created_at' => 'Дата создания',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalanceAction()
    {
        return $this->hasOne(BalanceAction::class, ['id' => 'id_balance_action']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalance()
    {
        return $this->hasOne(Balance::class, ['id' => 'id_tmc_balance']);
    }
}
