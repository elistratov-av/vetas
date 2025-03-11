<?php

namespace app\models\db;

use app\common\behaviors\TicketNumberBehavior;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\events\VisitToTransferEvent;
use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\common\models\VisitStatus;
use app\common\validators\VisitStatusValidator;
use app\models\behaviors\VisitBehavior;
use app\models\db\etp\ETPStatusLog;
use app\models\db\tmc\BalanceFlow;
use app\modules\soap\models\etp\status\Status1090;
use app\modules\v2\modules\vaccinationJournal\models\FlatModel;
use app\modules\v2\modules\visit\events\VisitEventHandler;
use app\modules\v2\modules\visit\events\VisitEventUpdateMany;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "public.visits".
 *
 * @property integer                   $id
 * @property string                    $status
 * @property boolean                   $is_paid
 * @property string                    $change_reason
 * @property integer                   $id_owner
 * @property integer|array             $id_pet                 @deprecated TODO - remove after fixing all queries, views, reports, etc.
 * @property integer                   $id_organization
 * @property integer                   $id_quarantine
 * @property integer                   $created_by
 * @property integer                   $updated_by
 * @property string                    $created_at
 * @property string                    $updated_at
 * @property string                    $fact_start_dttm
 * @property string                    $fact_end_dttm
 * @property integer                   $cooldown
 * @property integer                   $author
 * @property integer                   $number
 * @property string                    $time_range
 * @property integer                   $channel
 * @property integer                   $source
 * @property integer                   $duration
 * @property string                    $ticket_number
 * @property string                    $start_dttm
 * @property string                    $time_range_without_cooldown
 * @property string                    $type
 * @property string                    $visit_to_address
 * @property string                    $description
 * @property string                    $cancel_initiator
 * @property string                    $time_signed
 * @property boolean                   $is_signed
 * @property int                       $id_sign
 * @property boolean                   $is_veteran
 * @property boolean                   $is_disabled
 * @property boolean                   $is_blind
 * @property boolean                   $is_orphan
 * @property boolean                   $is_large_family
 * @property boolean                   $is_veteran_of_labour
 * @property string                    $preferences_document
 * @property boolean                   $is_agreed_pers_data
 * @property string                    $variety
 * @property boolean                   $flagVisitToSpecialist
 * @property int                       $vaccination_station_id
 * @property string                    $agreement_rejected_at
 * @property boolean                   $is_for_unauth_client    Приём создан для неавторизованного клиента
 * @property string                    $payment_request_uid     uid запроса на оплату к ЕПШ (Единый платёжный шлюз)
 * @property string                    $payment_request_date    Время запроса на оплату
 * @property string                    $payment_link            Ссылка на оплату в ЕПШ для клиент (Issue 718. Храним без особой необходимости)
 * @property string                    $guid_video              Идентификатор для формирования ссылки на видеоконференцию для телевета
 *
 * @property-read VaccinationStation   $vaccinationStation
 * @property-read Organizations        $organization
 * @property-read GovServices[]        $services
 * @property-read VisitsGovServices[]  $visitsGovServices
 * @property-read VisitsGovServices    $visitsGovService
 * @property-read VisitsSpecialists    $visitsSpecialist
 * @property-read VisitsSpecialists[]  $visitsSpecialists
 * @property-read Specialists          $specialists
 * @property-read VisitDescriptions[]  $visitDescriptions
 * @property-read Specialists          $author_ref             Автор направления
 * @property-read Visits               $source_ref             Родительский прием
 * @property-read Visits[]             $child_visits           Дочерние приемы
 * @property-read VisitServiceTmc[]    $visitServiceTmcs
 * @property-read Pets[]               $pets
 * @property-read PetOwners            $owner                  Владелец животного
 * @property-read SignedVisitModel     $sign
 * @property-read UserModel            $updateAuthor           Автор последнего изменения приема (пользователь)
 * @property-read Specialists          $updateAuthorSpec       Автор последнего изменения приема (специалист)
 * @property-read Specialists          $updateAuthorSpecFirst  Автор последнего изменения приема (специалист, если прием изменем из другой организации)
 * @property-read Status1090           $cancelledByOwnerViaMosru
 * @property-read VisitServiceTmcPet[] $visitServiceTmcPet
 * @property-read Quarantine           $quarantine
 */
class Visits extends ActiveRecord
{
    const CANCEL_BY_TECH_REASON = 'Отменено по техническим причинам.';

    const TYPE_VISIT = 'VISIT';
    const TYPE_AT_HOME = 'AT_HOME';
    const TYPE_AMBULANCE = 'AMBULANCE';
    const TYPE_VISIT_VC = 'VISIT_VC';
    const TYPE_VISIT_VC_DETOUR = 'VISIT_VC_DETOUR';
    const TYPE_VISIT_VC_SHELTER = 'VISIT_VC_SHELTER';

    const INITIATOR_IS_OWNER = 'OWNER';
    const INITIATOR_IS_CLINIC = 'CLINIC';

    const VISIT_SINGLE = 'SINGLE'; //один
    const VISIT_MULTIPLE = 'MULTIPLE'; //несколько
    const VISIT_BROOD = 'BROOD'; //
    const TYPE_ONLINE = 'ONLINE';

    /**
     * флаг для определения в TicketNumberBehavior для определения того, что запись к специалисту
     *
     * @var bool
     */
    private $flagVisitToSpecialist = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.visits';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'is_paid',
                    'is_signed',
                    'is_veteran',
                    'is_blind',
                    'is_disabled',
                    'is_orphan',
                    'is_large_family',
                    'is_veteran_of_labour'
                ],
                'boolean'
            ],
            ['is_paid', 'default', 'value' => false],
            [['change_reason', 'payment_request_uid', 'guid_video'], 'string'],
            [
                [
                    'id_owner',
                    'id_organization',
                    'id_quarantine',
                    'cooldown',
                    'author',
                    'number',
                    'channel',
                    'source',
                    'vaccination_station_id'
                ],
                'integer'
            ],
            [['channel', 'variety'], 'required'],
            ['status', 'string', 'max' => 1],
            ['status', VisitStatusValidator::class],
            [
                ['id_organization'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Organizations::class,
                'targetAttribute' => ['id_organization' => 'id']
            ],
            [
                ['id_owner'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PetOwners::class,
                'targetAttribute' => ['id_owner' => 'id']
            ],
            [
                ['channel'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ShiftType::class,
                'targetAttribute' => ['channel' => 'id']
            ],
            [
                ['author'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Specialists::class,
                'targetAttribute' => ['author' => 'id']
            ],
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'time_signed'], 'safe'],
            ['start_dttm', 'safe'],
            [['fact_start_dttm', 'fact_end_dttm', 'payment_request_date'], 'safe'],
            [['time_range', 'time_range_without_cooldown'], 'safe'],
            ['source', 'exist', 'targetClass' => Visits::class, 'targetAttribute' => ['source' => 'id']],
            [['id_quarantine'], 'exist', 'skipOnError' => true, 'targetClass' => Quarantine::class, 'targetAttribute' => ['id_quarantine' => 'id']],
            [['id_quarantine'], 'required', 'when' => function($model) {
                return $model->type === self::TYPE_VISIT_VC_DETOUR;
            }],
            ['type', 'default', 'value' => self::TYPE_VISIT],
            ['type', 'in', 'range' => self::types()],
            [['visit_to_address', 'description', 'preferences_document'], 'filter', 'filter' => 'trim'],
            [['visit_to_address', 'description', 'preferences_document'], 'filter', 'filter' => 'strip_tags'],
            [['visit_to_address', 'description', 'payment_link'], 'string'],
            ['preferences_document', 'string', 'max' => 255],
            ['variety', 'default', 'value' => self::VISIT_SINGLE],
            ['variety', 'in', 'range' => [self::VISIT_SINGLE, self::VISIT_MULTIPLE, self::VISIT_BROOD]],
            [
                'visit_to_address',
                'required',
                'when' => function ($model) {
                    /* @var $model \app\models\db\Visits */
                    return $model->isOutboundVisit();
                },
            ],
            ['cancel_initiator', 'in', 'range' => self::cancelInitiatorTypes()],
            [
                'cancel_initiator',
                'required',
                'when' => function ($model) {
                    /* @var $model \app\models\db\Visits */
                    return $model->status == VisitStatus::CANCELED;
                },
                'message' => 'Не указан инициатор отмены приема',
            ],
            ['id_sign', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'ticket_number' => [
                'class' => TicketNumberBehavior::class
            ],
            'visit' => [
                'class' => VisitBehavior::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'is_paid' => 'Is Paid',
            'change_reason' => 'Change Reason',
            'id_owner' => 'Id Owner',
            'id_organization' => 'Id Organization',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'fact_start_dttm' => 'Fact Start Dttm',
            'fact_end_dttm' => 'Fact End Dttm',
            'cooldown' => 'Cooldown',
            'author' => 'Author',
            'time_range' => 'Time Range',
            'number' => 'Number',
            'channel' => 'Channel',
            'variety' => 'Variety'
        ];
    }

    /**
     * @param $insert
     * @param $changedAttributes
     * @return void
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->status === VisitStatus::TRANSFER
            && $changedAttributes['status'] !== VisitStatus::TRANSFER) {

            $role = Role::ROLE_SYSADMIN_GOS;
            $specialists = Specialists::find()
                ->select(['specialists.id id', 'u.email'])
                ->innerJoin('auth_assignment aa', "aa.id_specialist = specialists.id AND aa.item_name = '$role'")
                ->innerJoin('users u', 'u.id = specialists.id_user AND u.email IS NOT NULL')
                ->where([
                    'AND',
                    ['=', 'specialists.id_organization', $this->organization->id],
                    [
                        'OR',
                        ['>=', 'specialists.expel_date', (new \DateTime())->format('Y-m-d')],
                        new Expression('specialists.expel_date is null')
                    ]
                ])
                ->asArray()->all()
            ;

            foreach ($specialists as $specialist) {
                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new VisitToTransferEvent([
                    'count' => 1,
                    'id_visit' => $this->id,
                    'email' => $specialist['email'],
                ]));
            }
        }
    }

    /**
     * @return bool
     */
    public function isLiveQueue(): bool
    {
        /** @var ShiftType $shift */
        $shift = ShiftType::findOne(['id' => $this->channel]);
        return $shift->type == ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE;
    }

    /**
     * @return bool
     */
    public function isMosRu()
    {
        return ShiftType::find()->where([
            'AND',
            ['id' => $this->channel],
            ['IN', 'type', [
                ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT,
                ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME,
            ]]
        ])->exists();
    }

    /**
     * Проверка на возможность уведомления по приему
     * Увеодмления не отправляются на записи с mos.ru(уходят через ЕТП) и для живой очереди
     *
     * @return bool
     */
    public function canNotify(): bool
    {
        /**
         * Важно!!!
         * При изменении массива типов смен, по которым не возможно уведомление - необходимо изменить и в методе
         * v2/modules/visit/controllers/VisitTrait::findVisit()
         */

        /** @var ShiftType $shift */
        $shift = ShiftType::findOne(['id' => $this->channel]);

        //Оповещаем для мос.ру если тип визита онлайн (телевет)
        return $this->type == self::TYPE_ONLINE
            || !in_array(
                $shift->type,
                ShiftType::NOTIFY_DISABLED
            );
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->status === VisitStatus::FINISHED;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitDescriptions()
    {
        return $this->hasMany(VisitDescriptions::class, ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsSpecialist()
    {
        return $this->hasOne(VisitsSpecialists::class, ['id_visit' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialists()
    {
        return $this->hasOne(Specialists::class, ['id' => 'id_specialist'])->viaTable('visits_specialists',
            ['id_visit' => 'id']);
    }

    public function getVisitParamValues()
    {
        return $this->hasMany(VisitParamValues::class, ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmcs()
    {
        return $this->hasMany(VisitServiceTmc::class, ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
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
    public function getPets()
    {
        return $this->hasMany(Pets::class, ['id' => 'id_pet'])
            ->viaTable(VisitPets::tableName(), ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet'])
            ->viaTable(VisitPets::tableName(), ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor_ref()
    {
        return $this->hasOne(Specialists::class, ['id' => 'author']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource_ref()
    {
        return $this->hasOne(Visits::class, ['id' => 'source']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild_visits()
    {
        return $this->hasMany(Visits::class, ['source' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsGovServices()
    {
        return $this->hasMany(VisitsGovServices::class, ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsGovService()
    {
        return $this->hasOne(VisitsGovServices::class, ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(GovServices::class, ['id' => 'id_service'])->viaTable('visits_gov_services',
            ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsSpecialists()
    {
        return $this->hasMany(VisitsSpecialists::class, ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSign()
    {
        return $this->hasOne(SignedVisitModel::class, ['id' => 'id_sign']);
    }

    /**
     * @return bool
     */
    public function getFlagVisitToSpecialist(): bool
    {
        return $this->flagVisitToSpecialist;
    }

    /**
     * @param bool $flagVisitToSpecialist
     */
    public function setFlagVisitToSpecialist(bool $flagVisitToSpecialist): void
    {
        $this->flagVisitToSpecialist = $flagVisitToSpecialist;
    }

    /**
     * @return array
     */
    public static function types()
    {
        return [
            self::TYPE_VISIT,
            self::TYPE_AT_HOME,
            self::TYPE_AMBULANCE,
            self::TYPE_VISIT_VC,
            self::TYPE_VISIT_VC_DETOUR,
            self::TYPE_VISIT_VC_SHELTER,
            self::TYPE_ONLINE,
        ];
    }

    /**
     * @return array
     */
    public static function typeOptions()
    {
        return [
            self::TYPE_VISIT => 'Прием',
            self::TYPE_AT_HOME => 'Вызов на дом',
            self::TYPE_AMBULANCE => 'Вызов на дом (ВПД)',
            self::TYPE_VISIT_VC => 'Вакцинация на ПП',
            self::TYPE_VISIT_VC_DETOUR => 'Вакцинация на обходе',
            self::TYPE_VISIT_VC_SHELTER => 'Вакцинация в приюте',
            self::TYPE_ONLINE => 'Онлайн-консультация',

        ];
    }

    /**
     * @return bool
     */
    public function isRegularVisit()
    {
        return $this->type == self::TYPE_VISIT;
    }

    public function isVaccinationVisit()
    {
        return in_array($this->type, [
            self::TYPE_VISIT_VC,
            self::TYPE_VISIT_VC_DETOUR,
            self::TYPE_VISIT_VC_SHELTER,
        ]);
    }

    /**
     * @return bool
     */
    public function isAtHomeVisit()
    {
        return $this->type == self::TYPE_AT_HOME;
    }

    /**
     * @return bool
     */
    public function isOnlineVisit()
    {
        return $this->type == self::TYPE_ONLINE;
    }

    /**
     * @return bool
     */
    public function isAmbulanceVisit()
    {
        return $this->type == self::TYPE_AMBULANCE;
    }

    /**
     * @return bool
     */
    public function isOutboundVisit()
    {
        return in_array($this->type, [self::TYPE_AT_HOME, self::TYPE_AMBULANCE]);
    }

    /**
     * @return array
     */
    public static function cancelInitiatorTypes()
    {
        return [
            self::INITIATOR_IS_CLINIC,
            self::INITIATOR_IS_OWNER,
        ];
    }

    /**
     * @return array
     */
    public static function cancelInitiatorOptions()
    {
        return [
            self::INITIATOR_IS_CLINIC => 'Владелец',
            self::INITIATOR_IS_OWNER => 'Клиника',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateAuthor()
    {
        return $this->hasOne(UserModel::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVaccinationStation()
    {
        return $this->hasOne(VaccinationStation::class, ['id' => 'vaccination_station_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateAuthorSpec()
    {
        return $this->hasOne(Specialists::class, ['id_user' => 'updated_by'])
            ->andOnCondition([Specialists::tableName() . '.id_organization' => $this->id_organization]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateAuthorSpecFirst()
    {
        return $this->hasOne(Specialists::class, ['id_user' => 'updated_by'])
            ->orderBy(Specialists::tableName() . '.id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCancelledByOwnerViaMosru()
    {
        return $this->hasOne(ETPStatusLog::class, ['visit_id' => 'id'])
            ->andWhere(ETPStatusLog::tableName() . '.etp_status = :status', [':status' => Status1090::CODE]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnamnesis()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_ANAMNEZ_1']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHealth()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_CLINICAL_DATA']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClinicalSigns()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_KLINICHESKIE_PRIZNAKI']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreDiagnosis()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_PREDVARITELNYJ_DIAGNOZ_3']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFinDiagnosis()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTreatment()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_SKHEMA_LECHENIYA_5']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssurance()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_LECHEBNAYA_POMOSHCH']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommendations()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_visit' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->andWhere(['dt.tech_name' => 'VISIT_REKOMENDATSII']);
    }

    /**
     * Обертка updateAll для логирования изменений статуса Приема(ов)
     *
     * @param array        $attributes
     * @param string|array $condition
     * @param array        $params
     *
     * @return int|void
     */
    public static function updateAll($attributes, $condition = '', $params = []) {
        if (!array_key_exists('status', $attributes)) {
            return parent::updateAll($attributes, $condition, $params);
        }

        $visits = self::find()->select('id')->where($condition)->all();

        if (empty($visits)) {
            return 0;
        }

        $result = parent::updateAll($attributes, $condition, $params);
        (new VisitEventHandler())->handleUpdateMany((new VisitEventUpdateMany())->setVisitsId($visits));

        return $result;
    }
    /**
     * @return array
     */
    public static function varieties()
    {
        return [
            self::VISIT_SINGLE,
            self::VISIT_MULTIPLE,
            self::VISIT_BROOD,
        ];
    }

    /**
     * @return array
     */
    public static function varietyOptions()
    {
        return [
            self::VISIT_SINGLE => 'Одно животное',
            self::VISIT_MULTIPLE => 'Несколько животных',
            self::VISIT_BROOD => 'Выводок',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmcPet()
    {
        return $this->hasMany(VisitServiceTmcPet::class, ['id_visit' => 'id']);
    }

    /**
     * Обёртка над $this->getVisitDescriptions() для соблюдения
     * схемы именования атрибутов в объектах (snake_case)
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVisit_descriptions()
    {
        return $this->getVisitDescriptions();
    }

    public function delete()
    {
        foreach($this->visitsGovServices as $service) {
            VisitServiceTmc::deleteAll([
                'AND',
                ['id_visit' => $this->id],
                ['id_visits_gov_service' => $service->id],
            ]);

            // Удаляем списания
            BalanceFlow::deleteAll([
                'AND',
                ['id_visit_service' => $service->id],
            ]);
        }

        parent::delete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuarantine()
    {
        return $this->hasOne(Quarantine::class, ['id_quarantine' => 'id']);
    }

    /**
     * @return string
     */
    public function getGovServicesAsString(): string
    {
        $types = [];
        foreach($this->visitsGovServices as $govService) {
            $types[] = $govService->service->name;
        }
        $uniqueTypes = array_unique($types);
        $uniqueTypesCount = count($uniqueTypes);
        $i = 0;
        $asString = '';
        foreach($uniqueTypes as $uniqueType) {
            $asString .= "$uniqueType";
            if (++$i !== $uniqueTypesCount) {
                $asString .= ", \n";
            }
        }

        return $asString;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->where(['entity_type' => 'visit']);
    }

    public function getMosruFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->where(['entity_type' => 'visit-mos-ru']);
    }

    /**
     *  Метод для получения номера телефона владельца животного
     *
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function getOwnerPhoneContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id_owner'])->where([
            'and',
            ['contacts.entity_type' => 'pet_owner'],
            ['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_PHONE])],
        ]);
    }

}



