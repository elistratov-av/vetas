<?php

namespace app\models\db\etp;

use app\models\db\Visits;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class ETPMessage
 *
 * @property integer $visit_id
 * @property string $service_number
 * @property string $phone
 * @property string $email
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $message
 */
class ETPMessage extends ActiveRecord
{
    public static function tableName()
    {
        return 'etp.message';//etp.message
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class' => TimestampBehavior::class,
                'value' => date("Y-m-d H:i:s"),
            ]
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::class, ['id' => 'visit_id']);
    }
}
