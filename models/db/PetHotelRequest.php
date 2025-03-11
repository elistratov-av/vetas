<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "pet_hotel_request".
 *
 * @property int $id
 * @property int $id_status
 * @property int $id_owner
 * @property int $id_animal
 * @property int $id_room
 * @property string $date_from
 * @property string $date_to
 * @property string $notes
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class PetHotelRequest extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_hotel_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_status', 'id_owner', 'id_animal', 'id_room', 'date_from', 'date_to'], 'required'],
            [['date_from', 'date_to'], 'datetime', 'format' => 'php:Y-m-d'],
            [['notes'], FullTrimValidator::class],
            [['notes', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_status', 'id_owner', 'id_animal', 'id_room', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'animal_weight', 'count_feeding', 'animal_diet_dry', 'animal_diet_wet', 'feeding_rate', 'count_walking'], 'safe'],
            ['date_from', function ($attribute, $params, $validator) {
                $df = new \DateTime($this->date_from);
                $dt = new \DateTime($this->date_to);

                $diff = $dt->getTimestamp() - $df->getTimestamp();
                if ($diff < 0) {
                    $this->addError($attribute, "Указан неверный промежуток времени: с {$this->date_from} по {$this->date_to}");
                }
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_status' => 'Статус заявки',
            'id_owner' => 'ID владельца',
            'id_animal' => 'ID питомца',
            'id_room' => 'Помещение',
            'date_from' => 'Начало периода',
            'date_to' => 'Окончание периода',
            'animal_weight' => 'Масса',
            'count_feeding' => 'Количество кормлений',
            'animal_diet_dry' => 'Рацион cухой(гр.)',
            'animal_diet_wet' => 'Рацион влажный(гр.)',
            'feeding_rate' => 'Норма кормления',
            'count_walking' => 'Количество прогулок',
            'notes' => 'Примечание',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(PetHotelRequestStatus::class, ['id_status' => 'id']);
    }

}
