<?php

namespace app\modules\soap\v2\models\wsdl;

use yii\helpers\Inflector;

/**
 * Trait HandlerTrait
 * @package app\modules\soap\v2\models\wsdl
 */
trait HandlerTrait
{
    /**
     * @param array $arr
     * @return mixed
     */
    protected static function convertKeyCase($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = self::convertKeyCase($value);
                $arr[$key] = $value;
            }
            if (!is_int($key)) {
                $newKey = Inflector::id2camel($key, '_');
                if ($newKey !== $key) {
                    $arr[$newKey] = $value;
                    unset($arr[$key]);
                }
            }
        }

        return $arr;
    }
}
