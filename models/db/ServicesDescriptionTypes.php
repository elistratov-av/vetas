<?php

namespace app\models\db;

/**
 * This is the model class for table "services_description_types".
 *
 * @property int $id
 * @property int $id_service ID услуги
 * @property int $id_description_type ID поля данных приема
 * @property bool $required Обязательность заполнения поля данных приема
 *
 * @property DescriptionTypes $descriptionType
 * @property GovServices $service
 */
class ServicesDescriptionTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'services_description_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_service', 'id_description_type'], 'required'],
            [['id_service', 'id_description_type'], 'integer'],
            [['required'], 'boolean'],
            [['required'], 'default', 'value' => false],
            [['id_description_type'], 'exist', 'skipOnError' => true, 'targetClass' => DescriptionTypes::class, 'targetAttribute' => ['id_description_type' => 'id']],
            [['id_service'], 'exist', 'skipOnError' => true, 'targetClass' => GovServices::class, 'targetAttribute' => ['id_service' => 'id']],
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
            'id_description_type' => 'Id Description Type',
            'required' => 'Required',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptionType()
    {
        return $this->hasOne(DescriptionTypes::class, ['id' => 'id_description_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(GovServices::class, ['id' => 'id_service']);
    }
}
