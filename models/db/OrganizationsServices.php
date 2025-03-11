<?php

namespace app\models\db;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "organizations_services".
 *
 * @property int $id_organization
 * @property int $id_service
 * @property int $id_service_type
 * @property string $service_type_name
 * @property int $service_type_sort
 * @property string $service_name
 * @property int $service_sort
 * @property string $service_price
 * @property int $service_duration
 * @property int $service_si
 * @property string $service_si_name
 * @property bool $sign
 * @property string $reason
 */
class OrganizationsServices extends ActiveRecord
{
    public static function primaryKey()
    {
        return ['id_organization', 'id_service'];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'organizations_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_organization', 'id_service', 'id_service_type', 'service_type_sort', 'service_sort', 'service_duration', 'service_si'], 'default', 'value' => null],
            [['id_organization', 'id_service', 'id_service_type', 'service_type_sort', 'service_sort', 'service_duration', 'service_si'], 'integer'],
            [['service_price'], 'number'],
            [['sign'], 'boolean'],
            [['reason'], 'string'],
            [['service_type_name', 'service_name', 'service_si_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_organization' => 'Id Organization',
            'id_service' => 'Id Service',
            'id_service_type' => 'Id Service Type',
            'service_type_name' => 'Service Type Name',
            'service_type_sort' => 'Service Type Sort',
            'service_name' => 'Service Name',
            'service_sort' => 'Service Sort',
            'service_price' => 'Service Price',
            'service_duration' => 'Service Duration',
            'service_si' => 'Service Si',
            'service_si_name' => 'Service Si Name',
            'sign' => 'Sign',
            'reason' => 'Reason',
        ];
    }
}
