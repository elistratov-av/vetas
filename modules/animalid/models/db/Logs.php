<?php


namespace app\modules\animalid\models\db;

use app\models\db\ActiveRecord;

class Logs extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'animalid.logs';
    }
}
