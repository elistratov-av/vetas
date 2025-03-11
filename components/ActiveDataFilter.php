<?php

namespace app\components;

class ActiveDataFilter extends \yii\data\ActiveDataFilter
{
    protected function validateOperatorCondition($operator, $condition, $attribute = null)
    {
        $attributeTypes = $this->getSearchAttributeTypes();

        if ($attributeTypes[$attribute] === 'array' && is_string($condition) && strpos($condition, ',') !== false ) {
            $condition = array_map('trim', explode(',', $condition));
        }

        parent::validateOperatorCondition($operator, $condition, $attribute);
    }

}
