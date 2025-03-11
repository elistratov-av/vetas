<?php

namespace app\models\db\audit;

use app\models\db\ActiveRecord;

/**
 * Class LogExternalAuth
 * @package app\models\db\audit
 *
 * @property int    $id
 * @property string $service_name
 * @property string $protocol
 * @property string $interface
 * @property bool   $is_success
 * @property string $created_at
 * @property string $updated_at
 * @property string $ip
 */
class LogExternalAuth extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'audit.log_external_auth';
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
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'service_name' => 'Внешняя система',
            'is_success' => 'Результат',
            'protocol' => 'Протокол',
            'interface' => 'Интерфейс',
            'created_at' => 'Дата и время',
            'ip' => 'IP',
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
