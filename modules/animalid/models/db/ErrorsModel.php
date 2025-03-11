<?php

namespace app\modules\animalid\models\db;

use app\models\db\ActiveRecord;

/**
 * @property int    $id
 * @property array  $data
 * @property string $action
 * @property string $type
 * @property string $reason
 * @property string $timestamp
 */
class ErrorsModel extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'animalid.errors';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['data', 'action', 'type', 'reason'], 'safe'],
        ];
    }
}
