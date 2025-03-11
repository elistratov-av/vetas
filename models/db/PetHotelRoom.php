<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "pet_hotel_room".
 *
 * @property int $id
 * @property string $name
 * @property string $notes
 * @property string $purpose
 * @property int $id_pet_hotel
 * @property float $area
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class PetHotelRoom extends ActiveRecord
{
    public $pet_hotel_name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_hotel_room';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'id_pet_hotel'], 'required'],
            [['name', 'notes', 'purpose'], FullTrimValidator::class],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['area'], 'default', 'value' => 0],
            [['id_pet_hotel', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'id_pet_hotel' => 'Зоогостиница',
            'area' => 'Площадь',
            'notes' => 'Доп.информация',
            'purpose' => 'Назначение помещения',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetHotel()
    {
        return $this->hasOne(PetHotel::class, ['id' => 'id_pet_hotel']);
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields[] = 'pet_hotel_name';
        return $fields;
    }
}
