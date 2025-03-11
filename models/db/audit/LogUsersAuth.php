<?php

namespace app\models\db\audit;

use app\models\db\ActiveRecord;

/**
 * Class LogUsersAuth
 * @package app\models\db\audit
 *
 * @property int    $id
 * @property string $login
 * @property int    $id_user
 * @property int    $target
 * @property int    $type
 * @property bool   $is_success
 * @property string $ip
 * @property string $ua
 * @property string $created_at
 * @property string $updated_at
 */
class LogUsersAuth extends ActiveRecord
{
    const TARGET_FRONTEND = 1;
    const TARGET_ADMIN = 2;
    const TARGET_ANDROID = 3;
    const TARGET_VETADMIN = 4;
    const TARGET_CALLCENTER = 5;

    const TYPE_LOGIN = 1;
    const TYPE_LOGOUT = 2;

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'audit.log_users_auth';
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
            self::TARGET_FRONTEND => 'фронт',
            self::TARGET_ANDROID => 'андроид',
            self::TARGET_ADMIN => 'админка',
            self::TARGET_VETADMIN => 'ветадминка',
            self::TARGET_CALLCENTER => 'колл-центр',
        ];
    }

    /**
     * @return array
     */
    public static function typeOptions()
    {
        return [
            self::TYPE_LOGIN => 'вход',
            self::TYPE_LOGOUT => 'выход',
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
