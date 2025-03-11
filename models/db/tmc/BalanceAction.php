<?php

namespace app\models\db\tmc;

use app\common\definitions\BalanceActionTmcListWorDefinition;
use app\models\db\ActiveRecord;
use app\models\db\Organizations;
use app\models\db\Specialists;

/**
 * Действия над ТМЦ (передача, утилизация и тд)
 *
 * @property int $id
 * @property string $num Номер (для документов)
 * @property string $action Действие (передача на баланс, утилизация, списание, запрос на выдачу)
 * @property string $status Статус (новый, завершено, отклонено)
 * @property string $initiator_date Дата, когда создал
 * @property int $initiator_id_specialist Кто создал
 * @property string $initiator_comment Комментарий создателя
 * @property string $acceptor_date Когда согласился (отклонил)
 * @property string $acceptor_id_specialist Кто согласился (отклонил)
 * @property string $acceptor_comment Комментарий принявшего (отклонившего)
 * @property int $from_id_organization С баланса какой организации
 * @property int $from_id_specialist С баланса какого спеца
 * @property int $to_id_organization На баланс какой организации
 * @property int $to_id_specialist На баланс какого спеца
 * @property string $write_off_reason Тип причины списания
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $transfer_date  Фактическая дата передачи
 * @property string $receiving_date Фактическая дата получения
 *
 * @property BalanceActionTmcList[] $actionTmcList;
 * @property Specialists $acceptorSpecialist
 * @property Specialists $initiatorSpecialist
 * @property Specialists $fromSpecialist
 * @property Specialists $toSpecialist
 * @property Organizations $fromOrganization
 * @property Organizations $toOrganization
 * @property BalanceFlow[] $balanceFlows
 */
class BalanceAction extends ActiveRecord
{
    /**
     * Действия
     */
    const ACTION_TRANSFER_TO_BALANCE = 'transfer_to_balance';   //передача на баланс
    const ACTION_TRANSFER_REQUEST = 'transfer_request';         //запрос на TMC
    const ACTION_RECYCLING = 'recycling';                       //утилизация
    const ACTION_WRITE_OFF = 'write_off';                       //списание

    /**
     * Статусы
     */
    const STATUS_COMPLETED = 'C';               //Завершено/Выполнено
    const STATUS_REJECTED = 'R';                //Отклонено
    const STATUS_WAITING_EXECUTION = 'E';       //"Ожидание исполнения" передающего (кому адресовано)
    const STATUS_WAITING_CONFIRMATION = 'O';    //"Ожидание подтверждения" инициатором (кто создал запрос)

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.balance_action';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['initiator_date', 'acceptor_date', 'acceptor_id_specialist', 'created_at', 'updated_at', 'transfer_date', 'receiving_date'], 'safe'],
            [['initiator_id_specialist', 'from_id_organization'], 'required'],
            [['initiator_id_specialist', 'from_id_organization', 'from_id_specialist', 'to_id_organization', 'to_id_specialist', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['initiator_id_specialist', 'from_id_organization', 'from_id_specialist', 'to_id_organization', 'to_id_specialist', 'created_by', 'updated_by'], 'integer'],
            [['initiator_comment', 'acceptor_comment'], 'string', 'max' => 65535],
            ['num', 'string', 'max' => 256],
            ['action', 'string', 'max' => 32],
            [
                'action',
                'in',
                'range' => [
                    self::ACTION_TRANSFER_TO_BALANCE,
                    self::ACTION_TRANSFER_REQUEST,
                    self::ACTION_RECYCLING,
                    self::ACTION_WRITE_OFF,
                ]
            ],
            ['status', 'string', 'max' => 1],
            [
                'status',
                'in',
                'range' => [
                    self::STATUS_COMPLETED,
                    self::STATUS_REJECTED,
                    self::STATUS_WAITING_EXECUTION,
                    self::STATUS_WAITING_CONFIRMATION,
                ]
            ],
            ['write_off_reason', 'string', 'max' => 30],
            [['initiator_comment', 'acceptor_comment'], 'string', 'max' => 65535],
            ['write_off_reason', 'in', 'range' => BalanceActionTmcListWorDefinition::getCollection()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                      => 'ID',
            'num'                     => 'Номер',
            'action'                  => 'Действие',
            'status'                  => 'Статус',
            'initiator_date'          => 'Дата, когда создал',
            'initiator_id_specialist' => 'id специалиста, кто создал',
            'initiator_comment'       => 'Комментарий создателя',
            'acceptor_date'           => 'Дата, когда согласился (отклонил)',
            'acceptor_id_specialist'  => 'id специалиста, что согласился (отклонил)',
            'acceptor_comment'        => 'Комментарий принявшего (отклонившего)',
            'from_id_organization'    => 'id, с какой организации',
            'from_id_specialist'      => 'id, с какого специалиста',
            'to_id_organization'      => 'id, на какую организацию',
            'to_id_specialist'        => 'id, на какого специалиста',
            'write_off_reason'        => 'Тип причины списания',
            'created_at'              => 'Дата создания',
            'created_by'              => 'Кто создал',
            'updated_at'              => 'Дата обновления',
            'updated_by'              => 'Кто обновил',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActionTmcList()
    {
        return $this->hasMany(BalanceActionTmcList::class, ['id_action' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcceptorSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'acceptor_id_specialist']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInitiatorSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'initiator_id_specialist']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'from_id_specialist']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'to_id_specialist']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'from_id_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'to_id_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalanseFlows()
    {
        return $this->hasMany(BalanceFlow::class, ['id_balance_action' => 'id']);
    }

}
