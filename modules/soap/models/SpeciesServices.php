<?php

namespace app\modules\soap\models;

/**
 * This is the model class for table "species_services".
 *
 * @property int $id
 * @property int $id_species Ссылка на вид животного
 * @property int $id_service Ссылка на услугу
 *
 * @property Species $species
 */
class SpeciesServices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'species_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_species', 'id_service'], 'required'],
            [['id_species', 'id_service'], 'default', 'value' => null],
            [['id_species', 'id_service'], 'integer'],
            [['id_species', 'id_service'], 'unique', 'targetAttribute' => ['id_species', 'id_service']],
            [['id_species'], 'exist', 'skipOnError' => true, 'targetClass' => Species::className(), 'targetAttribute' => ['id_species' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_species' => 'Id Species',
            'id_service' => 'Id Service',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecies()
    {
        return $this->hasOne(Species::className(), ['id' => 'id_species']);
    }
}
