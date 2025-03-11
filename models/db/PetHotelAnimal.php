<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "pet_hotel_animal".
 *
 * @property int $id
 * @property int $id_owner
 * @property string $nickname
 * @property string $type
 * @property string $breed
 * @property boolean $gender_male
 * @property string $processing
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class PetHotelAnimal extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_hotel_animal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_owner', 'nickname'], 'required'],
            [['nickname', 'type', 'breed', 'processing'], FullTrimValidator::class],
            [['type', 'breed', 'processing', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['gender_male'], 'default', 'value' => true],
            [['gender_male'], 'boolean'],
            [['id_owner', 'created_by', 'updated_by'], 'integer'],
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
            'id_owner' => 'Владелец',
            'nickname' => 'Кличка',
            'type' => 'Вид',
            'breed' => 'Порода',
            'gender_male' => 'Пол',
            'processing' => 'Обработки',
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
        return $this->hasOne(PetHotel::class, ['id_pet_hotel' => 'id']);
    }

}
