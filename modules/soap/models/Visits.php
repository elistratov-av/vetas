<?php

namespace app\modules\soap\models;

use app\common\behaviors\TicketNumberBehavior;
use app\models\db\Params;
use app\models\db\Pets;
use app\models\db\VisitPets;
use app\models\db\VisitServiceParamValues;
use app\modules\soap\models\etp\ETPMessage;
use app\modules\v2\modules\visit\events\VisitEventHandler;
use yii\base\BaseObject;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * This is the model class for table "visits".
 *
 * @property boolean $flagVisitToSpecialist
 * @property integer $id
 * @property string $status
 * @property boolean $is_paid
 * @property string $change_reason
 * @property integer $id_owner
 * @property integer|array $id_pet
 * @property integer $id_organization
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $fact_start_dttm
 * @property string $fact_end_dttm
 * @property integer $cooldown
 * @property integer $author
 * @property integer $number
 * @property string $time_range
 * @property integer $channel
 * @property integer $source
 * @property integer $duration
 * @property string $ticket_number
 * @property string $start_dttm
 * @property string $time_range_without_cooldown
 * @property string  $type
 * @property string  $visit_to_address
 * @property string $description
 * @property string $cancel_initiator
 * @property string $variety
 * @property boolean $call_to_home
 * @property boolean $is_for_unauth_client Приём создан для неавторизованного клиента
 * @property string $guid_video            Идентификатор для формирования ссылки на видеоконференцию для телевета
 * @property string $payment_request_uid   uid запроса на оплату к ЕПШ (Единый платёжный шлюз)
 * @property string $payment_request_date  Время запроса на оплату
 * @property string $payment_link          Ссылка на оплату в ЕПШ для клиент (Issue 718. Храним без особой необходимости)
 *
 * @property MosruOrganizations $organization
 * @property PetOwners $owner
 * @property VisitsGovServices[] $visitsGovServices
 * @property MosruServices[] $services
 * @property MosruSpecialists[] $specialists
 * @property ETPMessage $etpMessage
 * @property-read Pets[] $pets
 */
class Visits extends ActiveRecord
{
    const
        CALL_TO_HOME_CHANGE_TIME = 60 * 60 * 6, // 6 часов
        IN_CLINIC_CHANGE = 60 * 60 * 2, // 2 часа
        CALL_TO_HOME_BEFORE_TIME = 60, // 1 час
        CALL_TO_HOME_AFTER_TIME = 60 // 1 час
    ;

    /** @var MosruServices[]|null */
    protected $_services;

    /** @var  MosruSpecialists */
    protected $_specialist;

    /** @var boolean */
    public $call_to_home;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors[] = [
            'class' => TicketNumberBehavior::class
        ];

        $behaviors[] = [
            'class' => BlameableBehavior::class,
        ];

        $behaviors[] = [
            'class' => TimestampBehavior::class,
            'value' => date("Y-m-d H:i:s"),
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_paid'], 'boolean'],
            [['duration', 'id_owner', 'id_pet', 'id_organization', 'created_by', 'updated_by', 'fact_start_dttm', 'fact_end_dttm', 'cooldown', 'author', 'payment_request_uid', 'payment_request_date'], 'default', 'value' => null],
            [['duration', 'id_owner', 'id_pet', 'id_organization', 'created_by', 'updated_by', 'cooldown', 'author', 'channel', 'number', 'start_specialization'], 'integer'],
            [['start_dttm', 'fact_start_dttm', 'fact_end_dttm', 'payment_request_date'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['change_reason', 'payment_request_uid'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 255],
            //['status', VisitStatusValidator::class],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => MosruOrganizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_owner' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            ['source', 'exist', 'targetClass' => Visits::class, 'targetAttribute' => ['source' => 'id']],
            ['type', 'default', 'value' => \app\models\db\Visits::TYPE_VISIT],
            ['type', 'in', 'range' => \app\models\db\Visits::types()],
            [['visit_to_address', 'description'], 'filter', 'filter' => 'trim'],
            [['visit_to_address', 'description'], 'filter', 'filter' => 'strip_tags'],
            [['visit_to_address', 'description', 'payment_link'], 'string'],
            [
                'visit_to_address',
                'required',
                'when' => function ($model) {
                    /* @var $model \app\models\db\Visits */
                    return in_array($model->type, [\app\models\db\Visits::TYPE_AT_HOME]);
                },
            ],
            ['variety', 'default', 'value' => \app\models\db\Visits::VISIT_SINGLE],
            ['variety', 'in', 'range' => [\app\models\db\Visits::VISIT_SINGLE, \app\models\db\Visits::VISIT_MULTIPLE, \app\models\db\Visits::VISIT_BROOD]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'is_paid' => 'Is Paid',
            'start_dttm' => 'Start Dttm',
            'duration' => 'Duration',
            'change_reason' => 'Change Reason',
            'id_owner' => 'Id Owner',
            'id_pet' => 'Id Pet',
            'id_organization' => 'Id Organization',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'fact_start_dttm' => 'Fact Start Dttm',
            'fact_end_dttm' => 'Fact End Dttm',
            'cooldown' => 'Cooldown',
            'author' => 'Author',
            'channel' => 'Channel',
            'number' => 'Number',
        ];
    }

    /**
     * @return bool
     */
    public function isLiveQueue(): bool
    {
        return false;
    }

    /**
     * @return false|null|string
     * @throws \yii\db\Exception
     */
    public function getOldTicketNumber()
    {
        $command = \Yii::$app->db->createCommand(
            "select ticket_number from visits_history where id_visit = :id_visit order by id desc limit 1",
            [
                'id_visit' => $this->id
            ]
        );
        return $command->queryScalar();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(MosruOrganizations::class, ['id' => 'id_organization']);
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
    public function getVisitsGovServices()
    {
        return $this->hasMany(VisitsGovServices::class, ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(MosruServices::class, ['id' => 'id_service'])->viaTable('visits_gov_services', ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialists()
    {
        return $this->hasMany(MosruSpecialists::class, ['id_specialist' => 'id_specialist'])
            ->viaTable('visits_specialists', ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEtpMessage()
    {
        return $this->hasOne(ETPMessage::class, ['visit_id' => 'id']);
    }

    /**
     * @param bool $call_to_home
     * @return string
     */
    public function getDate($call_to_home = false)
    {
        $date = (new \DateTime($this->start_dttm));
        if ($call_to_home) {
            $date->add(new \DateInterval("PT60M"));
        }
        return $date->format(DATE_RFC3339);
    }

    /**
     * @param bool $call_to_home
     * @return string
     */
    public function getSlot($call_to_home = false)
    {
        $date = (new \DateTime($this->start_dttm));
        if ($call_to_home) {
            $date->add(new \DateInterval("PT60M"));
        }
        return $date->format('H:i');
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
     * @param MosruSpecialists $specialist
     */
    public function setSpecialist(MosruSpecialists $specialist)
    {
        $this->_specialist = $specialist;
    }

    /**
     * @param $services
     */
    public function setServices($services)
    {
        $this->_services = $services;
        $this->duration = self::getDurationByServices($this->_services, $this->call_to_home);
        $this->cooldown = self::getCooldownByServices($this->_services, $this->call_to_home);
        $this->start_dttm = self::getStartDttm($this->start_dttm, $this->call_to_home);
    }

    static public function getDurationByServices($services, bool $call_to_home)
    {
        $duration = 0;
        foreach ($services as $service) {
            $duration += $service->duration;
        }
        if ($call_to_home) {
            // +2 часа (120 минут) для вызова на дом
            $duration += Visits::CALL_TO_HOME_BEFORE_TIME + Visits::CALL_TO_HOME_AFTER_TIME;
        }

        return $duration;
    }

    static public function getCooldownByServices($services, bool $call_to_home)
    {
        $cooldown = 0;
        if ($call_to_home) {
            return $cooldown;
        }
        foreach ($services as $service) {
            $cooldown += $service->cooldown;
        }
        return $cooldown;
    }

    static public function getTotalVisitLengthMinutes($services, bool $call_to_home)
    {
        return self::getDurationByServices($services, $call_to_home)
            + self::getCooldownByServices($services, $call_to_home);
    }

    static public function getStartDttm($start_dttm, bool $call_to_home)
    {
        /**
         * В случае вызова на дом считаем что время 13:30 - это время к которому ожидается приезд врача
         * поэтому интервал времени визита начинаем раньше на время равное Visits::CALL_TO_HOME_BEFORE_TIME (1час),
         * т.е.с 12:30
         */
        if (!$call_to_home) {
            return $start_dttm;
        }
        $date = new \DateTime($start_dttm);
        $interval = new \DateInterval("PT" . Visits::CALL_TO_HOME_BEFORE_TIME . "M");
        $interval->invert = 1;
        $date->add($interval);
        $start_dttm = $date->format('Y-m-d H:i:s');

        return $start_dttm;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->_services) {
            $this->unlinkAll('services', true);
            foreach($this->_services as $service) {
                $visitService = new VisitsGovServices();
                $visitService->id_visit = $this->id;
                $visitService->id_service = $service->id;
                $visitService->count = 1;
                $visitService->id_pet = $this->id_pet;

                $visitService->save(false);
                $this->saveVisitParams($visitService);
            }
        }

        if ($this->_specialist) {
            $this->unlinkAll('specialists', true);
            $this->link('specialists', $this->_specialist);
        }

        \Yii::$app->db
            ->createCommand()
            ->delete(VisitPets::tableName(), ['id_visit' => $this->id])
            ->execute();

        $link = new VisitPets([
            'id_visit' => $this->id,
            'id_pet' => $this->id_pet,
        ]);

        if (!$link->save()) {
            $this->addError('id_pet', 'Ошибка при сохранении связи животного с приемом');

            return false;
        }

        if ($insert || array_key_exists('status', $changedAttributes)) {
			(new VisitEventHandler())->handle(new AfterSaveEvent([
				'changedAttributes' => $changedAttributes,
				'sender'            => $this,
			]));
		}

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     */
    public function isOnlineVisit()
    {
        return $this->type == \app\models\db\Visits::TYPE_ONLINE;
    }

    /**
     * @param VisitsGovServices $visitService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function saveVisitParams(VisitsGovServices $visitService)
    {
        if ($this->call_to_home && !empty($this->visit_to_address)) {
            if ($param = Params::findOne(['tech_name' => 'P0_Juraddress'])) {
                $serviceParam = new VisitServiceParamValues();
                $serviceParam->id_visitservice = $visitService->id;
                $serviceParam->id_param = $param->id;
                $serviceParam->char_value = $this->visit_to_address;
                $serviceParam->save(false);
            }
        }
    }

}
