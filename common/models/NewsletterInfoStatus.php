<?php

namespace app\common\models;

class NewsletterInfoStatus
{
    public const NOT_SENT = 2;
    public const IS_SENT = 1;

    public static function list(): array
    {
        return [
            self::NOT_SENT,
            self::IS_SENT,
        ];
    }
}
