<?php

namespace app\modules\admin\models;


use yii\db\ActiveRecord;

/**
 * Class StatusLog
 * @package app\modules\admin\models
 */
class StatusLog extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'etp.status_log';
    }
}
