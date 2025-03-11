<?php

namespace app\models\db;

/**
 * Class Color
 * @package app\models\db
 *
 * @property int $id
 * @property int $species_id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class Color extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return 'public.colors';
    }
}
