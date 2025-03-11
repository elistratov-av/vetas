<?php

namespace app\models\db\found_pet;

use app\models\db\ActiveRecord;

/**
 * Class Message
 * @package app\models\db\found_pet
 *
 * @property int          $id
 * @property string       $type
 * @property string       $service_number
 * @property array|string $body
 * @property array|string $headers
 * @property string       $created_at
 * @property string       $updated_at
 * @property bool         $is_success
 * @property array|string $ad_errors
 */
class Message extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'found_pet.messages';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [$this->attributes(), 'safe']
        ];
    }
}
