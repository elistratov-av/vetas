<?php

namespace app\models\db;

/**
 * Class RecoveryPassword
 * @package app\models\db
 *
 * @property int    $id
 * @property int    $status
 * @property string    $login
 * @property string $date

 */
class RecoveryPassword extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.recovery_password';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['login', 'string'],
        ];
    }
}