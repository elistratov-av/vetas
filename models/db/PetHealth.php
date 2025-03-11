<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pet_health".
 *
 * @property int $id
 * @property int $id_pet
 * @property string $status
 * @property string $date
 * @property double $temperature
 * @property double $weight
 * @property string $anamnesis
 * @property int $id_organization
 * @property int $id_specialist
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Pets $pet
 * @property Organizations $organization
 * @property Specialists $specialist
 * @property Users $creater
 * @property Users $updater
 */
class PetHealth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_health';
    }

    const HEALTH_STATUS_QUARANTINE = 'QUARANTINE';
    const HEALTH_STATUS_QUARANTINE_PR = 'QUARANTINE_PR';
    const HEALTH_STATUS_HEALTHY = 'HEALTHY';

    const HEALTH_STATUSES = [
        self::HEALTH_STATUS_QUARANTINE => 'Карантин',
        self::HEALTH_STATUS_QUARANTINE_PR => 'Карантин (пр)',
        self::HEALTH_STATUS_HEALTHY => 'В вольере',
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pet', 'status', 'date', 'id_organization', 'created_by', 'created_at'], 'required'],
            [['id_organization', 'id_pet', 'id_specialist', 'created_by', 'updated_by'], 'integer'],
            [['temperature', 'weight'], 'double'],
            [['created_at', 'updated_at', 'protected_at',], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['status', 'anamnesis'], 'string', 'max' => 255],
            ['id_organization', 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            ['id_pet', 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            ['id_specialist', 'exist', 'skipOnError' => true, 'targetClass' => Specialists::class, 'targetAttribute' => ['id_specialist' => 'id']],
            ['created_by', 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['created_by' => 'id']],
            ['updated_by', 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['updated_by' => 'id']],
            [['anamnesis'], 'filter', 'filter' => 'trim'],
            [['anamnesis'], 'filter', 'filter' => 'strip_tags'],
            ['status', 'in', 'range' => array_keys(self::HEALTH_STATUSES)],
        ];
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
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'id_specialist']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreater()
    {
        return $this->hasOne(Users::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(Users::class, ['id' => 'updated_by']);
    }
}
