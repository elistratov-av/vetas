<?php

namespace app\modules\admin\models;

use yii\db\ActiveRecord;

/**
 * Class Message
 * @package app\modules\admin\models
 */
class Message extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'etp.message';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::className(), ['id' => 'visit_id']);
    }
}
