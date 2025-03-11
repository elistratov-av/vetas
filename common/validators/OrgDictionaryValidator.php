<?php

namespace app\common\validators;

use yii\db\Query;
use yii\validators\Validator;

class OrgDictionaryValidator extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $tableName = $model->getAttribute('is_out_org') ? 'outside_org' : 'organizations';
        $id_organization = $model->getAttribute($attribute);

        $check = (new Query())
            ->from($tableName)
            ->where(["$tableName.id" => $id_organization])
            ->count();
        if (!$check) {
            $this->addError($model, $attribute, "Выбрано не существующее сочетание значений id_organization и is_out_org");
        }
    }
}
