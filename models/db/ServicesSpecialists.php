<?php

namespace app\models\db;

/**
 * This is the model class for table "services_specialists".
 *
 * @property int $id
 * @property int $id_service
 * @property int $id_specialist
 * @property int $id_organization
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property GovServices $service
 * @property Organizations $organization
 * @property Specialists $specialist
 */
class ServicesSpecialists extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'services_specialists';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_service', 'id_specialist', 'id_organization'], 'required'],
            [['id_service', 'id_specialist', 'id_organization', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_service', 'id_specialist', 'id_organization', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_service', 'id_specialist', 'id_organization'], 'unique', 'targetAttribute' => ['id_service', 'id_specialist', 'id_organization']],
            [['id_service'], 'exist', 'skipOnError' => true, 'targetClass' => GovServices::class, 'targetAttribute' => ['id_service' => 'id']],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => Specialists::class, 'targetAttribute' => ['id_specialist' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_service' => 'Id Service',
            'id_specialist' => 'Id Specialist',
            'id_organization' => 'Id Organization',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(GovServices::class, ['id' => 'id_service']);
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
}
