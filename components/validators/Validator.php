<?php

namespace app\components\validators;

/**
 * Class Validator
 * @package app\components\validators
 */
class Validator extends \yii\validators\Validator
{
    const INHERIT_MODE_MERGE_ALL = 'merge all';
    const INHERIT_MODE_MERGE_ATTRIBUTES = 'merge attributes';
    const INHERIT_MODE_REPLACE_ALL = 'replace all';
    const INHERIT_MODE_REPLACE_ATTRIBUTES = 'replace attributes';

    /**
     * @param array $parentRules
     * @param array $childRules
     * @param string $defaultInheritMode
     * @return array
     */
    public static function merge(
        $parentRules = [],
        $childRules = [],
        $defaultInheritMode = self::INHERIT_MODE_MERGE_ALL
    ) {
        $result = [];

        foreach ($parentRules as $key => $parentRule) {

            $resultAttributes = $resultValidator = $resultOptions = [];

            $parentAttributes = array_shift($parentRule);
            $parentValidator = array_shift($parentRule);
            $parentOptions = $parentRule;

            if (isset($childRules[$key])) {

                $childRule = $childRules[$key];

                $childAttributes = array_shift($childRule);
                $childValidator = array_shift($childRule);
                $childOptions = $childRule;

                $inheritMode = isset($childOptions['inheritMode']) && !empty($childOptions['inheritMode'])
                    ? $childOptions['inheritMode']
                    : $defaultInheritMode;

                /**
                 * if keys are not same - replace parent only if INHERIT_MODE_REPLACE_ALL
                 * else add child Rule at end;
                 */
                if ($parentValidator != $childValidator) {
                    if ($inheritMode == self::INHERIT_MODE_REPLACE_ALL) {
                        $result[$key] = [$childAttributes, $childValidator] + $childOptions;
                    } else {
                        $result[$key] = [$parentAttributes, $parentValidator] + $parentOptions;
                        $result[] = [$childAttributes, $childValidator] + $childOptions;
                    }
                } else {
                    switch ($inheritMode) {

                        case self::INHERIT_MODE_MERGE_ALL:

                            $resultAttributes = array_unique(array_merge($parentAttributes, $childAttributes));
                            $resultValidator = $childValidator;
                            $resultOptions = array_merge($parentOptions, $childOptions);

                            break;

                        case self::INHERIT_MODE_MERGE_ATTRIBUTES:

                            $resultAttributes = array_unique(array_merge($parentAttributes, $childAttributes));
                            $resultValidator = $childValidator;
                            $resultOptions = $parentOptions;

                            break;

                        case self::INHERIT_MODE_REPLACE_ALL:

                            $resultAttributes = $childAttributes;
                            $resultValidator = $childValidator;
                            $resultOptions = $childOptions;

                            break;

                        case self::INHERIT_MODE_REPLACE_ATTRIBUTES:

                            $resultAttributes = $childAttributes;
                            $resultValidator = $parentValidator;
                            $resultOptions = $parentOptions;

                            break;

                    }

                    $result[$key] = [$resultAttributes, $resultValidator] + $resultOptions;
                }

                unset($childRules[$key]);

            } else {
                $result[$key] = [$parentAttributes, $parentValidator] + $parentOptions;
                continue;
            }

        }

        if (count($childRules)) {
            $result = array_merge($result, $childRules);
        }

        return $result;
    }
}
