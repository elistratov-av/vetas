<?php

namespace app\modules\soap\models;

use app\common\helpers\TsRangeHelper;
use app\models\db\ActiveRecord;

/**
 * @property string $service_number
 * @property integer $id_specialist
 * @property string $time_range
 * @property string $created_at
 *
 * @property-read MosruSpecialists $specialist
 */
class Booking extends ActiveRecord
{
    // Константа учитывается в коде, но в случае изменения значения нужно будет изменить psql-вью слотов расписания
    // (смотри миграцию m230707_053705_add_mos_ru_booking)
    const BOOKING_MINUTES_LIFETIME = 15;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mosru.booking';
    }

    public function rules()
    {
        return [
            [['service_number', 'id_specialist', 'time_range'], 'required'],
            [['id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => MosruSpecialists::class, 'targetAttribute' => ['id_specialist' => 'id_specialist']],
            ['time_range', 'safe'],
//            ['time_range', function ($attribute, $params, $validator) {
//                return $this->$attribute === 'empty';
//            }],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(MosruSpecialists::class, ['id_specialist' => 'id_specialist']);
    }

    /**
     * @param MosruSpecialists $specialist
     */
    public function setSpecialist(MosruSpecialists $specialist)
    {
        $this->id_specialist = $specialist->id_specialist;
    }

    public function setTimeRange(string $timeRangeString)
    {
        $this->time_range = $timeRangeString;
    }

    public function isActive()
    {
        $lifetime = self::BOOKING_MINUTES_LIFETIME;
        $now = new \DateTime();
        $expirationDate = (new \DateTime($this->created_at))->modify("+$lifetime minutes");

        return $now < $expirationDate;
    }

    public function isSameSlots(string $startDttm, int $minutesLength)
    {
        return $this->time_range === TsRangeHelper::buildTsRange($startDttm, $minutesLength);
    }
}
