<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use app\common\validators\ViolationStateValidator;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "violation".
 *
 * @property int $id_violation
 * @property string $state Состояние нарушения: N - новое, W – в работе, C - отменено, F - завершено
 * @property int $id_owner
 * @property int $id_pet
 * @property int $id_type
 * @property int $id_disease
 * @property int $id_ARV
 * @property int $id_veterinarian Id из users; Заполняется при фиксации нарушения на приеме
 * @property int $id_visit Заполняется при фиксации нарушения на приеме
 * @property int $id_cancellation Причина отмены работы над нарушением
 * @property int $id_quarantine Карантин в рамках которого выставено нарушение
 * @property string $date_violation Дата фиксации нарушения
 * @property string $date_plan Планируемая дата для ликвидации нарушения (вакцинации или идентификации животного)
 * @property string $rejection_reason Заполняется при отказе от вакцинации, идентификаци
 * @property string $comment Описание нарушения
 * @property string $cancellation_details Заполняется при варианте причины отмены работы над нарушениями «Иное»
 * @property string $feedback_token Токен доступа к форме обратной связи по нарушению
 *
 * @property Diseases $disease
 * @property PetOwners $owner
 * @property Pets $pet
 * @property Users $veterinarian
 * @property ViolationAdminRights $aRV
 * @property ViolationCancellation $cancellation
 * @property ViolationType $type
 * @property Visits $visit
 * @property ViolationHistory[] $violationHistories
 * @property Files[] $files
 * @property Order[] $orders
 * @property Order $last_order Последнее предписание по нарушению
 * @property ViolationToARV[] $arvs Административно Правовые Нарушения
 */
class Violation extends ActiveRecord
{
    /**
     * N - новое (DEPRECATED)
     */
    const STATE_NEW = 'N';

    /**
     * W – в работе(1)
     */
    const STATE_IN_WORK = 'W';

    /**
     * A - подтверждено
     */
    const STATE_ACCEPTED = 'A';

    /**
     * C - отменено
     */
    const STATE_CANCELED = 'C';

    /**
     * F - завершено
     */
    const STATE_FINISHED = 'F';

    /**
     * V - на проверке
     */
    const STATE_ON_VERIFY = 'V';

    /**
     * Статусы, в которых редактирование запрещено
     */
    const READ_ONLY_STATES = [
        self::STATE_FINISHED,
        self::STATE_CANCELED
    ];

    /**
     * Статусы, в которых нарушение считается активным
     */
    const ACTIVE_STATES = [
        self::STATE_NEW,
        self::STATE_IN_WORK,
        self::STATE_ON_VERIFY,
        self::STATE_ACCEPTED
    ];

    /**
     * Допустимые переходы статусов
     */
    const TRANSITIONS_STATES = [
        self::STATE_NEW => [
            self::STATE_IN_WORK,
            self::STATE_CANCELED
        ],
        self::STATE_IN_WORK => [
            self::STATE_FINISHED,
            self::STATE_CANCELED
        ],
        self::STATE_ON_VERIFY => [
            self::STATE_IN_WORK,
            self::STATE_CANCELED,
            self::STATE_ACCEPTED,
            self::STATE_FINISHED,
        ],
        self::STATE_ACCEPTED => [
            self::STATE_IN_WORK,
            self::STATE_CANCELED,
            self::STATE_FINISHED,
        ],
        self::STATE_CANCELED => false,
        self::STATE_FINISHED => false,
    ];

    /**
     * Все статусы
     */
    const AVAILABLE_STATES = [
        self::STATE_NEW,
        self::STATE_IN_WORK,
        self::STATE_CANCELED,
        self::STATE_FINISHED,
        self::STATE_ON_VERIFY,
        self::STATE_ACCEPTED
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->attachBehavior(
            'date_violation',
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s'),
                'createdAtAttribute' => 'date_violation',
                'updatedAtAttribute' => false
            ]
        );

        parent::init();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && $this->type->type === ViolationType::TYPE_VACCINATION_VIOLATION && $this->id_disease === null) {
            $rabies_id = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one()->id;
            $this->id_disease = $rabies_id;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'violation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['state', 'id_owner', 'id_pet', 'id_type', 'date_violation'], 'required'],
            [['id_owner', 'id_pet', 'id_type', 'id_disease', 'id_ARV', 'id_veterinarian', 'id_visit', 'id_cancellation', 'id_quarantine'], 'default', 'value' => null],
            [['id_owner', 'id_pet', 'id_type', 'id_disease', 'id_ARV', 'id_veterinarian', 'id_visit', 'id_cancellation', 'id_quarantine'], 'integer'],
            [['date_violation', 'date_plan'], 'safe'],
            [['comment'], FullTrimValidator::class],
            [['rejection_reason', 'comment', 'cancellation_details', 'feedback_token'], 'string'],
            [['state'], 'string', 'max' => 1],
            [['id_disease'], 'exist', 'skipOnError' => true, 'targetClass' => Diseases::class, 'targetAttribute' => ['id_disease' => 'id']],
            [['id_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_owner' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['id_veterinarian'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['id_veterinarian' => 'id']],
            [['id_ARV'], 'exist', 'skipOnError' => true, 'targetClass' => ViolationAdminRights::class, 'targetAttribute' => ['id_ARV' => 'id_ARV']],
            [['id_quarantine'], 'exist', 'skipOnError' => true, 'targetClass' => Quarantine::class, 'targetAttribute' => ['id_quarantine' => 'id']],
            //[['id_cancellation'], 'exist', 'skipOnError' => true, 'targetClass' => ViolationCancellation::class, 'targetAttribute' => ['id_cancellation' => 'id_cancellation']],
            ['id_cancellation', function ($attribute, $params, $validator) {
                if ($this->id_cancellation !== NULL) {

                    /** @var ViolationCancellation $violation_cancellation */
                    $violation_cancellation = ViolationCancellation::find()
                        ->where([
                            'id_cancellation' => $this->id_cancellation
                        ])->one();

                    if (empty($violation_cancellation)) {
                        $this->addError($attribute, 'id_cancellation с указанным id не существует');
                        return;
                    }

                    /*
                     * Некоторым типам необходимо заполнять поле cancellation_details
                     */
                    if ($violation_cancellation->is_need_cancellation_details == true && empty($this->cancellation_details)) {
                        $this->addError($attribute, 'Необходимо указать причину отмены');
                    }
                }
            }],
            [['id_pet'], function ($attribute) {
                if ($this->isNewRecord || $this->isAttributeChanged('id_pet')) {
                    // Проверяем условие для новых нарушений, но разрешаем закрывать или закрывать
                    if ($this->pet->id_main_pet && in_array($this->state, [self::STATE_ACCEPTED, self::STATE_ON_VERIFY])) {
                        $this->addError($attribute, 'Нельзя завести нарушение на дубль животного');
                    }
                    if ($this->pet->id_reg_expire_reason && in_array($this->state, [self::STATE_ACCEPTED, self::STATE_ON_VERIFY])) {
                        $this->addError($attribute, 'Нельзя завести нарушение на снятое с учёта животное');
                    }
                }
            }],
            [['id_type'], 'exist', 'skipOnError' => true, 'targetClass' => ViolationType::class, 'targetAttribute' => ['id_type' => 'id_type']],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::class, 'targetAttribute' => ['id_visit' => 'id']],
            ['state', ViolationStateValidator::class],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_violation' => 'Id Violation',
            'state' => 'Состояние нарушения',
            'id_owner' => 'Id Owner',
            'id_pet' => 'Id Pet',
            'id_type' => 'Id Type',
            'id_disease' => 'Id Disease',
            'id_ARV' => 'АПН',
            'id_veterinarian' => 'Id из users; Заполняется при фиксации нарушения на приеме',
            'id_visit' => 'Заполняется при фиксации нарушения на приеме',
            'id_cancellation' => 'Причина отмены работы над нарушением',
            'date_violation' => 'Дата фиксации нарушения',
            'date_plan' => 'Планируемая дата для ликвидации нарушения (вакцинации или идентификации животного)',
            'rejection_reason' => 'Заполняется при отказе от вакцинации, идентификаци',
            'comment' => 'Описание нарушения',
            'cancellation_details' => 'Заполняется при варианте причины отмены работы над нарушениями «Иное»',
        ];
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        $old_state = $this->getOldAttribute('state');

        if (!empty($old_state) && in_array($old_state, [self::STATE_CANCELED, self::STATE_FINISHED])) {
            return true;
        }

        return false;
    }

    /**
     * Есть ли активные нарушения по данному животному
     * @return bool
     * @throws InvalidConfigException
     */
    public function isPetHasActiveViolation()
    {
        if (empty($this->id_pet)) {
            throw new InvalidConfigException('Системная ошибка: не заполнен id_pet');
        }

        if (empty($this->id_type)) {
            throw new InvalidConfigException('Системная ошибка: не заполнен id_type');
        }

        return self::find()
            ->where([
                'AND',
                ['id_type' => $this->id_type],
                ['id_pet' => $this->id_pet],
                ['IN', 'state', self::ACTIVE_STATES]
            ])->exists();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDisease()
    {
        return $this->hasOne(Diseases::class, ['id' => 'id_disease']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVeterinarian()
    {
        return $this->hasOne(Users::class, ['id' => 'id_veterinarian']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getARV()
    {
        return $this->hasOne(ViolationAdminRights::class, ['id_ARV' => 'id_ARV']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolation_admin_rights()
    {
        return $this->hasOne(ViolationAdminRights::class, ['id_ARV' => 'id_ARV']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCancellation()
    {
        return $this->hasOne(ViolationCancellation::class, ['id_cancellation' => 'id_cancellation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ViolationType::class, ['id_type' => 'id_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::class, ['id' => 'id_visit']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolationHistories()
    {
        return $this->hasMany(ViolationHistory::class, ['id_violation' => 'id_violation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspectors()
    {
        return $this
            ->hasMany(Users::class, ['id' => 'id_inspector'])
            ->viaTable('violation_history', ['id_violation' => 'id_violation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLast_violation_history_record()
    {
        return $this->hasOne(ViolationHistory::class, ['id_violation' => 'id_violation'])
            ->select(
                new Expression('DISTINCT ON (id_violation) *')
            )
            ->orderBy('id_violation, date DESC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLast_inspector()
    {
        return $this
            ->hasOne(Users::class, ['id' => 'id_inspector'])
            ->via('last_violation_history_record');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id_violation'])
            ->where(['entity_type' => static::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id_violation' => 'id_violation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArvs()
    {
        return $this->hasMany(ViolationToARV::class, ['id_violation' => 'id_violation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLast_order()
    {
        return $this->hasOne(Order::class, ['id_violation' => 'id_violation'])
            ->select(
                new Expression('DISTINCT ON (id_violation) *')
            )
            ->orderBy('id_violation, date_order DESC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner_feedback()
    {
        return $this->hasMany(OwnerFeedback::class, ['id_violation' => 'id_violation']);
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return in_array($this->state, [self::STATE_FINISHED, self::STATE_CANCELED]);
    }

    /**
     * @return bool
     */
    public function hasExpiredOrders()
    {
        if (count($this->orders) === 0) {
            return false;
        }
        /** @var Order $order */
        foreach ($this->orders as $order) {
            if (strtotime($order->date_to) < time()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuarantine()
    {
        return $this->hasOne(Quarantine::class, ['id' => 'id_quarantine']);
    }
}
