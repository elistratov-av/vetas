<?php

namespace app\models\db\etp;

use yii\db\ActiveRecord;

/**
 * Class ETPStatusLog
 * @package app\models\db\etp
 *
 * @property int $id
 * @property string $log_time
 * @property string $service_number
 * @property int $visit_id
 * @property string $etp_status
 * @property string $status
 * @property string $message
 */
class ETPStatusLog extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'etp.status_log';
    }
}
