<?php

namespace app\common\validators;

use app\common\models\VisitStatus;
use app\models\db\Visits;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

class VisitStatusValidator extends Validator
{
    public $skipOnEmpty = false;
    public $message = '{attribute} is invalid';

    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string                       $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $result = $this->validateValue($model->$attribute);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
            return;
        }

        $dirtyAttributes = $model->getDirtyAttributes();
        if (count($dirtyAttributes) == 1 && isset($dirtyAttributes['is_paid'])) {
            return;
        }

        if ($model->isNewRecord) {
            if ($model->getAttribute($attribute) != VisitStatus::NEW &&
                !in_array($model->getAttribute('type'), [
                    Visits::TYPE_VISIT_VC,
                    Visits::TYPE_VISIT_VC_DETOUR,
                    Visits::TYPE_VISIT_VC_SHELTER,
                ])
            ) {
                $this->addError($model, $attribute, $this->message);
            }
        } else {
            $old = $model->getOldAttribute($attribute);
            $new = $model->getAttribute($attribute);

            if ($old == $new) {
                return;
            }

            $switchTo = VisitStatus::availableSwitch($old);
            if ($switchTo === false) {
                $this->addError($model, $attribute,
                    '{attribute}: нельзя редактировать завершенный или отмененный прием');
                return;
            }

            if (!ArrayHelper::isIn($new, $switchTo)) {
                $this->addError($model, $attribute, '{attribute}: прием не может быть переключен в это состояние');
                return;
            }

            if ($new === VisitStatus::CANCELED && empty($model->change_reason)) {
                $this->addError($model, $attribute, 'Не указано описание причины отмены приема');
                return;
            }
        }
    }

    /**
     * @param mixed $value
     *
     * @return array|null
     */
    public function validateValue($value)
    {
        return ArrayHelper::isIn($value, VisitStatus::getStatusList()) ? null : [$this->message, []];
    }
}
