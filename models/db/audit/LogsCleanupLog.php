<?php

namespace app\models\db\audit;

use app\models\db\ActiveRecord;

/**
 * Class LogsCleanupLog
 * @package app\models\db\audit
 *
 * @property int    $id
 * @property string $table_name
 * @property int $records_count
 * @property bool   $is_success
 * @property string $date_from
 * @property string $date_to
 * @property string $created_at
 * @property string $updated_at
 */
class LogsCleanupLog extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'audit.logs_cleanup_log';
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
