<?php

namespace app\common\validators;

use Yii;
use yii\db\Query;
use yii\validators\Validator;
use app\modules\v1\actions\EntityActionTrait;
use app\common\components\entity\EntityInstance;

class DescriptionTypeValidator extends Validator
{
    use EntityActionTrait;

    /**
     * Проверка типа описания(id_description_type) на соответсвие типу описания(id) для этой сущности(entity_type) в description_types
     *
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $parent_entity = $this->getEntityNameFromRequest(Yii::$app->request);

        if (!$parent_entity) {
            return;
        }

        $parentEntityTypeName = EntityInstance::inflectTypeName($parent_entity);

        $check = (new Query())
            ->from('description_types')
            ->where(['id' => $model->$attribute, 'entity_type' => $parentEntityTypeName])
            ->count();

        if (!$check) {
            $this->addError($model, $attribute, 'Данный тип описания не соответствует ресурсу ' . $parentEntityTypeName );
            return;
        }
    }

}
