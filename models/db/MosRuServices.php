<?php

namespace app\models\db;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "mosru.services".
 *
 * @property int $id
 * @property int $id_pricelist
 * @property string $name
 * @property string $price
 * @property int $sort_by
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $id_service_type
 * @property int $id_cabinet_type
 * @property int $id_specialization
 * @property int $duration
 * @property int $id_service_measure
 * @property string $alternative_name
 * @property string $cod
 * @property int $cooldown
 * @property int $id_service_goal
 * @property bool $at_home
 * @property bool $at_clinic
 * @property string $type
 *
 * @property ServiceTypes $serviceType
 */
class MosRuServices extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mosru.services';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_pricelist', 'sort_by', 'created_by', 'updated_by', 'id_service_type', 'id_cabinet_type', 'id_specialization', 'duration', 'id_service_measure', 'cooldown', 'id_service_goal'], 'integer'],
            [['name'], 'string'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['at_home'], 'boolean'],
            [['alternative_name'], 'string', 'max' => 100],
            [['cod'], 'string', 'max' => 4],
            [['type'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_pricelist' => 'Id Pricelist',
            'name' => 'Name',
            'price' => 'Price',
            'sort_by' => 'Sort By',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'id_service_type' => 'Id Service Type',
            'id_cabinet_type' => 'Id Cabinet Type',
            'id_specialization' => 'Id Specialization',
            'duration' => 'Duration',
            'id_service_measure' => 'Id Service Measure',
            'alternative_name' => 'Alternative Name',
            'cod' => 'Cod',
            'cooldown' => 'Cooldown',
            'id_service_goal' => 'Id Service Goal',
            'at_home' => 'At Home',
            'type' => 'Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceTypes::class, ['id' => 'id_service_type']);
    }
}
