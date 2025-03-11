<?php

namespace app\models\db\audit;

use app\models\db\ActiveRecord;
use app\models\db\admin\AdminUser;

/**
 * Class LogUsersChange
 * @package app\models\db\audit
 *
 * @property int          $id
 * @property string       $login
 * @property int          $id_user
 * @property int          $target
 * @property int          $type
 * @property bool         $is_success
 * @property array|string $before
 * @property array|string $after
 * @property string       $created_at
 * @property string       $updated_at
 * @property string       $created_by
 * @property string       $updated_by
 *
 * @property \app\models\db\admin\AdminUser $creator
 */
class LogUsersChange extends ActiveRecord
{
    const TARGET_FRONTEND = 1;
    const TARGET_ADMIN = 2;

    const TYPE_CREATE = 1;
    const TYPE_EDIT = 2;
    const TYPE_DELETE = 3;

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'audit.log_users_change';
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
            self::TYPE_CREATE => 'создание',
            self::TYPE_EDIT => 'редактирование',
            self::TYPE_DELETE => 'удаление',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'created_by']);
    }
}
