<?php

namespace app\modules\admin\models;

use app\models\db\ShiftType;
use app\models\db\VisitPrice;

/**
 * Class Visits
 * @package app\modules\admin\models
 */
class Visits extends \app\models\db\Visits
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Specialist::class, ['id' => 'id_specialist'])
                    ->viaTable('visits_specialists', ['id_visit' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(Message::class, ['visit_id' => 'id']);
    }

    public function getVisitPrice()
    {
        return $this->hasOne(VisitPrice::class, ['id_visit' => 'id']);
    }

    public function getStatusLog()
    {
        return $this->hasMany(StatusLog::class, ['visit_id' => 'id']);
    }

    public function getShiftType()
    {
        return $this->hasOne(ShiftType::class, ['id' => 'channel']);
    }
}
