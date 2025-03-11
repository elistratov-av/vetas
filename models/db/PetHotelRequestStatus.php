<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "pet_hotel_request_status".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class PetHotelRequestStatus extends ActiveRecord
{
    const CODE_COMPLETE = 'Завершено';
    const CODE_CANCEL = 'Отменено';
    const CODE_ACTIVE = 'Активно';
    const CODE_BOOKED = 'Забронировано';
    const CODE_CONFIRMED_BOOKED = 'Подтверждённая бронь';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_hotel_request_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code', 'name'], FullTrimValidator::class],
            [['created_by', 'updated_by'], 'default', 'value' => null],
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
            'code' => 'Код',
            'name' => 'Название',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
