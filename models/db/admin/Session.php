<?php

namespace app\models\db\admin;

use app\models\db\ActiveRecord;

/**
 * Class Session
 * @package app\models\db\admin
 *
 * @property int $id
 * @property int $id_user
 * @property string $token_hash
 * @property string $valid_until
 * @property string $last_active_at
 * @property string $ip
 * @property string $ua
 * @property string $ua_hash
 * @property string $created_at
 * @property string $updated_at
 */
class Session extends ActiveRecord
{
    public static function primaryKey()
    {
        return ['id'];
    }
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'admin.sessions';
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
