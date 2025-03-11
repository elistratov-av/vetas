<?php

namespace app\modules\soap\validators;

use app\modules\soap\models\Visits;
use yii\db\ActiveRecord;
use yii\validators\Validator;

/**
 * Приверка даты/времени визита. Запись в клиинику возможно осуществить за 2 часа до начала приема
 *
 * Class VisitDateValidator
 * @package app\common\validators
 */
class VisitDateValidator extends Validator
{
    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if (strtotime($model->$attribute) < (time() + Visits::IN_CLINIC_CHANGE)) {
            $this->addError($model, $attribute, "Запись на прием в клинике возможна за 2 часа до начала");
        }
    }

}
