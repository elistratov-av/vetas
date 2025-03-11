<?php

namespace app\common\components\reports\definitions;

use app\common\definitions\AbstractDefinition;

/**
 * Версии отчетов
 *
 * Class VersionDefinition
 *
 * @package app\common\components\reports\definitions
 * @author Aleksandr Roik
 */
class VersionDefinition extends AbstractDefinition
{
    /**
     * Список версий отчетов
     */
    const V1 = 'v1';

    /**
     * @var array
     */
    protected static $collection = [
        self::V1,
    ];
}
