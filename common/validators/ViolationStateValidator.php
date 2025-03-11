<?php


namespace app\common\validators;

use app\models\db\Violation;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

class ViolationStateValidator extends Validator
{
    public $skipOnEmpty = false;
    public $message = '{attribute} is invalid';

    /**
     * @param Violation $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if ($model->isNewRecord) {
            if (!in_array($model->getAttribute($attribute), [Violation::STATE_ACCEPTED, Violation::STATE_ON_VERIFY])) {
                $this->addError($model, $attribute, $this->message);
            }
        } else {
            $old = $model->getOldAttribute($attribute);
            $new = $model->getAttribute($attribute);

            if ($old == $new) {
                return;
            }

            $switchTo = Violation::TRANSITIONS_STATES[$old];
            if ($switchTo === false) {
                $this->addError($model, $attribute, '{attribute}: нельзя редактировать завершенное или отмененное нарушение');
                return;
            }

            if (!ArrayHelper::isIn($new, $switchTo)) {
                $this->addError($model, $attribute, '{attribute}: нарушение не может быть переключено в это состояние');
                return;
            }
        }
    }

    /**
     * @param mixed $value
     * @return array|null
     */
    public function validateValue($value)
    {
        return ArrayHelper::isIn($value, Violation::AVAILABLE_STATES) ? null : [$this->message, []];
    }
}
