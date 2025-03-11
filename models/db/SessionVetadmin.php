<?php

namespace app\models\db;

/**
 * Class SessionVetadmin
 * @package app\models\db\admin
 *
 * @property string $id
 * @property int    $expire
 * @property string $data
 * @property int    $id_user
 * @property string $valid_until
 * @property string $last_active_at
 * @property string $ip
 * @property string $ua
 * @property string $ua_hash
 * @property string $created_at
 * @property string $updated_at
 */
class SessionVetadmin extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'public.sessions_vetadmin';
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
