<?php

namespace app\common\validators;

use app\models\db\Organizations;
use yii\validators\Validator;

/**
 * Class OrganizationsLoopValidator
 * @package app\common\validators
 */
class OrganizationsLoopValidator extends Validator
{
    /**
     * @param \app\models\db\Organizations $model
     * @param string                       $attribute (атрибут parent_id)
     */
    public function validateAttribute($model, $attribute)
    {
        if (empty($model->$attribute)) {
            return;
        }

        if ($model->id == $model->$attribute) {
            $this->addError($model, $attribute, 'Организация не может быть родительской сама для себя');
            return;
        }

        $finished = false;
        $parent_id = $model->$attribute;

        while ($finished !== true) {
            $parent = Organizations::findOne(['id' => $parent_id]);
            if ($parent === null || empty($parent->$attribute)) {
                $finished = true;
            } else {
                if ($parent->id == $model->id) {
                    $this->addError($model, $attribute, 'Обнаружена циклическая связь в структуре подчиненности организаций');
                    return;
                }
                $parent_id = $parent->$attribute;
            }
        }
    }
}
