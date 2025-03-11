<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use DateInterval;
use DateTime;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class ShelterGuests
 * @package app\models\db
 *
 * @property int $id
 * @property int $id_organization
 * @property int $id_pet
 * @property string $arrival_date
 * @property string $arrival_comment
 * @property string $departure_date
 * @property string $departure_comment
 * @property string $departure_reason
 * @property string $status
 * @property string $quarantine_from
 * @property string $quarantine_to
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $id_owner
 * @property int $aviary_id
 * @property bool $is_quarantine
 * @property bool $is_catching_video
 * @property bool $socialized
 *
 * @property Organizations $organization
 * @property Pets $pet
 * @property PetOwners $pet_owner
 * @property FiasAddresses $fiasAddress
 * @property Aviary $aviary
 * @property Documents[] $documents
 * @property Files[] $files
 * @property PetHealth[] $petHealths
 */
class ShelterGuests extends ActiveRecord
{
    const STATUS_QUARANTINE = 'QUARANTINE';
    const STATUS_QUARANTINE_OTHER = 'QUARANTINE_OTHER';
    const STATUS_IN_SHELTER = 'IN_SHELTER';
    const STATUS_IN_HOSPITAL = 'IN_HOSPITAL';
    const STATUS_IN_ISOLATION = 'IN_ISOLATION';
    const STATUS_DEPARTURED = 'DEPARTURED';

    const STATUSES = [
        self::STATUS_QUARANTINE => 'Карантин',
        self::STATUS_QUARANTINE_OTHER => 'Карантин (пр)',
        self::STATUS_IN_SHELTER => 'В приюте',
        self::STATUS_IN_HOSPITAL => 'В стационаре',
        self::STATUS_IN_ISOLATION => 'В изоляторе',
        self::STATUS_DEPARTURED => 'Выбыл',
    ];

    const EXPORT_REPORT_ANIMAL_CARD = 'ANIMAL_CARD';
    const EXPORT_REPORT_ANKETA = 'ANKETA';
    const EXPORT_REPORT_TRANSFER = 'TRANSFER';
    const EXPORT_REPORT_RETURN = 'RETURN';
    const EXPORT_REPORT_DEATH = 'DEATH';

    const EXPORT_REPORTS = [
        self::EXPORT_REPORT_ANIMAL_CARD => 'Карточка учета животного',
        self::EXPORT_REPORT_ANKETA => 'Анкета желающего взять животное',
        self::EXPORT_REPORT_TRANSFER => 'Договор передачи',
        self::EXPORT_REPORT_RETURN => 'Акт возврата',
        self::EXPORT_REPORT_DEATH => 'Акт смерти',
    ];

    const DEPARTURE_REASON_RETURNED_TO_NEW_OWNER = 'RETURNED_TO_NEW_OWNER';
    const DEPARTURE_REASON_RETURNED_TO_OWNER = 'RETURNED_TO_OWNER';
    const DEPARTURE_REASON_DEATH = 'DEATH';
    const DEPARTURE_REASON_EUTHANASIA = 'EUTHANASIA';
    const DEPARTURE_REASON_ESCAPE = 'ESCAPE';

    const DEPARTURE_REASONS = [
        self::DEPARTURE_REASON_RETURNED_TO_NEW_OWNER => 'Передача новому владельцу',
        self::DEPARTURE_REASON_RETURNED_TO_OWNER => 'Возврат прежнему владельцу',
        self::DEPARTURE_REASON_DEATH => 'Естественная смерть',
        self::DEPARTURE_REASON_EUTHANASIA => 'Эвтаназия',
        self::DEPARTURE_REASON_ESCAPE => 'Побег',
    ];

    const ARRIVAL_REASON_CATCH = 'CATCH';
    const ARRIVAL_REASON_COURT_DECISION = 'COURT_DECISION';
    const ARRIVAL_REASON_FOUNDLING = 'FOUNDLING';
    const ARRIVAL_REASON_OWNER_REFUSAL = 'OWNER_REFUSAL';

    const ARRIVAL_REASONS = [
        self::ARRIVAL_REASON_CATCH => 'Отлов',
        self::ARRIVAL_REASON_COURT_DECISION => 'Решение суда',
        self::ARRIVAL_REASON_FOUNDLING => 'Подкидыш',
        self::ARRIVAL_REASON_OWNER_REFUSAL => 'Отказ владельца',
    ];

    const ARRIVAL_REASON_REPORT = [
        self::ARRIVAL_REASON_CATCH => 'с места отлова',
        self::ARRIVAL_REASON_COURT_DECISION => 'по решению суда',
        self::ARRIVAL_REASON_FOUNDLING => 'животное оставлено без надзора',
        self::ARRIVAL_REASON_OWNER_REFUSAL => 'владелец отказался от права собственности',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shelter_guests';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_organization', 'id_pet', 'is_quarantine', 'is_catching_video'], 'required'],
            [['id_organization', 'id_pet', 'aviary_id','departure_specialist'], 'integer'],
            [['socialized', ], 'boolean'],
            [['arrival_date', 'departure_date'], 'date', 'format' => 'php:Y-m-d'],
            ['id_organization', 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            ['id_pet', 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['arrival_comment', 'departure_comment'], 'string'],
            [['arrival_comment', 'departure_comment'], 'filter', 'filter' => 'trim'],
            [['arrival_comment', 'departure_comment'], 'filter', 'filter' => 'strip_tags'],
            [['arrival_comment', 'departure_comment'], FullTrimValidator::class],
            ['status', 'in', 'range' => array_keys(self::STATUSES)],
            ['departure_reason', 'in', 'range' => array_keys(self::DEPARTURE_REASONS)],
            [
                'id_owner',
                'exist',
                'skipOnError' => true,
                'targetClass' => PetOwners::class,
                'targetAttribute' => ['id_owner' => 'id'],
                'message' => 'Указанный владелец не существует',
            ],
            [
                'id_owner',
                'required',
                'when' => function ($model) {
                    /** @var $model ShelterGuests */
                    return $model->departure_reason == ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER;
                },
                'enableClientValidation' => false,
                'message' => 'Необходимо указать владельца животного',
            ],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],


            [['is_quarantine', ], 'boolean'],
            [['quarantine_from', 'departure_date'], 'date', 'format' => 'php:Y-m-d'],
            [['quarantine_to', 'departure_date'], 'date', 'format' => 'php:Y-m-d'],
            [['arrival_act_number', 'arrival_work_order', 'catching_act_number', 'catching_video'], 'string'],
            [['arrival_act_number_date', 'departure_date'], 'date', 'format' => 'php:Y-m-d'],
            [['arrival_work_order_date', 'departure_date'], 'date', 'format' => 'php:Y-m-d'],
            [
                'catching_act_number',
                'required',
                'when' => function ($model) {
                    /** @var $model ShelterGuests */
                    return $model->arrival_reason === ShelterGuests::ARRIVAL_REASON_CATCH && empty($model->departure_reason);
                },
                'enableClientValidation' => false,
                'message' => 'Необходимо указать номер акта отлова',
            ],
            [['catching_act_date', 'departure_date'], 'date', 'format' => 'php:Y-m-d'],
            ['catching_address', 'exist', 'skipOnError' => true, 'targetClass' => FiasAddresses::class, 'targetAttribute' => ['catching_address' => 'id']],
            [['is_catching_video', ], 'boolean'],
            ['aviary_id', 'exist', 'skipOnError' => true, 'targetClass' => Aviary::class, 'targetAttribute' => ['aviary_id' => 'id']],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }

    /**
     * @return ActiveQuery
     */
    public function getFiasAddress()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'catching_address']);
    }


    /**
     * @return ActiveQuery
     */
    public function getAviary()
    {
        return $this->hasOne(Aviary::class, ['id' => 'aviary_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPet_owner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner']);
    }

    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity_type' => self::tableName()]);;
    }

    public function getDocuments()
    {
        return $this->hasMany(Documents::class, ['file_id' => 'id'])
            ->via('files');;
    }

    public function getPetHealths()
    {
        return $this->hasMany(PetHealth::class, ['id_pet' => 'id_pet']);
    }

    public function addQuarantineDays($delete_existed = false) {
        if ($this->quarantine_from && $this->quarantine_to) {
            $pet_history = new PetHistory([
                'id_pet' => $this->id_pet,
                'event' => PetHistory::HISTORY_EVENT_QUARANTINE,
                'id_organization' => $this->id_organization,
                'created_by' => Yii::$app->user->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$pet_history->save()) {
                $this->addErrors($pet_history->getErrors());

                return false;
            }

            $temp_date = DateTime::createFromFormat('Y-m-d', $this->quarantine_from);
            $end_date = DateTime::createFromFormat('Y-m-d', $this->quarantine_to);
            while ($temp_date <= $end_date) {
                $date = $temp_date->format('Y-m-d');

                $pet_health = PetHealth::findOne(['date' => $date]);

                if ($pet_health && $delete_existed) {
                    $pet_health->delete();
                }

                $pet_health = new PetHealth([
                    'id_pet' => $this->id_pet,
                    'status' => PetHealth::HEALTH_STATUS_QUARANTINE,
                    'date' => $date,
                    'id_organization' => $this->id_organization,
                    'created_by' => Yii::$app->user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                if (!$pet_health->save()) {
                    $this->addErrors($pet_health->getErrors());

                    return false;
                }

                $temp_date->add(new DateInterval('P1D'));
            }
        }

        return true;
    }
}
