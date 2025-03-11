<?php

namespace app\models;

use app\interfaces\ActiveRecordInterface;
use app\traits\ActiveRecordTrait;

abstract class BaseActiveRecord extends \yii\db\ActiveRecord implements ActiveRecordInterface
{
    use ActiveRecordTrait {
        rules as protected _rulesTrait;
        attributes as protected _attributesTrait;
        setIsNewRecord as protected _setIsNewRecordTrait;
        setAttributes as protected _setAttributesTrait;
    }

    /**
     * for declaration compatibility
     * {@inheritDoc}
     */
    public function rules(
        $rules = [],
        $update = false
    )
    {
        return self::_rulesTrait($rules, $update);
    }

    // for declaration compatibility

    /** {@inheritDoc} */
    public function attributes(
        $only = null,
        $except = null,
        $schemaOnly = false
    )
    {
        return self::_attributesTrait($only, $except, $schemaOnly);
    }

    // for declaration compatibility

    /** {@inheritDoc} */
    public function setIsNewRecord(
        $setAsNewRecord = true
    )
    {
        self::_setIsNewRecordTrait($setAsNewRecord);
    }

    // for declaration compatibility

    /** {@inheritDoc} */
    public function setAttributes(
        $values,
        $safeOnly = true,
        $throwExceptionOnUnsafe = false
    )
    {
        self::_setAttributesTrait($values, $safeOnly, $throwExceptionOnUnsafe);
    }

}
