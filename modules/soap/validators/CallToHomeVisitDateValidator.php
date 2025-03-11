<?php

namespace app\modules\soap\validators;

use app\modules\soap\models\Visits;
use yii\db\ActiveRecord;
use yii\validators\Validator;

/**
 * Приверка даты/времени вызова на дом. Вызов на дом возможно осуществить за 6 часов до начала приема
 *
 * Class VisitDateValidator
 * @package app\common\validators
 */
class CallToHomeVisitDateValidator extends Validator
{
    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if (strtotime($model->$attribute) < (time() + Visits::CALL_TO_HOME_CHANGE_TIME)) {
            $this->addError($model, $attribute, "Вызов врача на дом возможен за 6 часа до начала");
        }
    }

}
