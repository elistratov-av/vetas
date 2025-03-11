<?php

namespace app\modules\foundPet\service;

use app\models\db\found_pet\BadWords;
use Wkhooy\ObsceneCensorRus;

class CensorService
{

    /** @var string */
    private static $badWords;

    /**
     * @param string $text
     *
     * @return int 1 - валидно 0 - возможно -1 - явно невалидно
     */
    public static function check(string $text): int
    {
        self::$badWords = implode('|', self::getReferenceBadWords());
        if (self::clearlyObscene($text)) {
            return -1;
        }
        if (self::possiblyObscene($text) || ObsceneCensorRus::isAllowed($text) === false) {
            return 0;
        }
        return 1;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public static function clearlyObscene(string $text): bool
    {
        return $text !== preg_replace('/\b(' . self::$badWords . ')\b/xui', '', $text);
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public static function possiblyObscene(string $text): bool
    {
        return $text !== preg_replace('/(' . self::$badWords . ')/xui', '', $text);
    }

    /**
     * @return array
     */
    private static function getReferenceBadWords(): array
    {
        return BadWords::find()
            ->select('name')
            ->asArray()
            ->column();
    }
}