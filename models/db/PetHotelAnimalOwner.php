<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "pet_hotel_animal_owner".
 *
 * @property string $i_fio
 * @property string $o_fio
 * @property string $f_fio
 * @property string $phone_number
 * @property string $address
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class PetHotelAnimalOwner extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_hotel_animal_owner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['i_fio', 'f_fio', 'phone_number'], 'required'],
            [['i_fio', 'o_fio', 'f_fio', 'phone_number', 'address'], FullTrimValidator::class],
            [['o_fio', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
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
            'i_fio' => 'Имя',
            'o_fio' => 'Отчество',
            'f_fio' => 'Фамилия',
            'phone_number' => 'Телефон',
            'address' => 'Адрес',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
