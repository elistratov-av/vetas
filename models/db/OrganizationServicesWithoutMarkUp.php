<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "organization_services_without_mark_up".
 *
 * @property int $id
 * @property int $id_organization
 * @property int $id_service
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property GovServices $service
 * @property Organizations $organization
 */
class OrganizationServicesWithoutMarkUp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'organization_services_without_mark_up';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_organization', 'id_service'], 'required'],
            [['id_organization', 'id_service', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_organization', 'id_service', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [
                ['id_organization'],
                'unique', 'targetAttribute' => ['id_organization', 'id_service'],
                'message' => 'Дублирующиеся значения id_organization, id_service'
            ],
            [['id_service'], 'exist', 'skipOnError' => true, 'targetClass' => GovServices::class, 'targetAttribute' => ['id_service' => 'id']],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_organization' => 'Id Organization',
            'id_service' => 'Id Service',
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
}
