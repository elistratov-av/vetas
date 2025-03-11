<?php

namespace app\models\db\audit;

use app\models\db\ActiveRecord;

/**
 * Class LogUsersBlockAuto
 * @package app\models\db\audit
 *
 * @property int    $id
 * @property string $login
 * @property int    $id_user
 * @property int    $target
 * @property int    $type
 * @property bool   $is_success
 * @property string $created_at
 * @property string $updated_at
 * @property string $block_until
 */
class LogUsersBlockAuto extends ActiveRecord
{
    const TARGET_FRONTEND = 1;
    const TARGET_ADMIN = 2;

    const TYPE_AUTH_ERROR = 1;
    const TYPE_INACTIVITY = 2;

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'audit.log_users_block_auto';
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

    /**
     * @return array
     */
    public static function targetOptions()
    {
        return [
            self::TARGET_FRONTEND => 'функциональная',
            self::TARGET_ADMIN => 'привилегированная',
        ];
    }

    /**
     * @return array
     */
    public static function typeOptions()
    {
        return [
            self::TYPE_AUTH_ERROR => 'неправильный логин/пароль',
            self::TYPE_INACTIVITY => 'по неактивности',
        ];
    }

    /**
     * @return array
     */
    public static function successOptions()
    {
        return [
            0 => 'неудача',
            1 => 'успех',
        ];
    }
}
