<?php

namespace app\common\components\reports\definitions;

use app\common\definitions\AbstractDefinition;

/**
 * Типы создаваемых файлов
 * Class FileTypeDefinition
 *
 * @package app\common\components\reports\definitions
 * @author Aleksandr Roik
 */
class FileTypeDefinition extends AbstractDefinition
{
    const EXCEL = 'xls';
    const PDF = 'pdf';

    /**
     * @var array
     */
    protected static $collection = [
        self::EXCEL,
        self::PDF,
    ];
}
