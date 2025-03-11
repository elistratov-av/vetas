<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;

/**
 * Class OdopmArea
 * @package app\models\db\odopm
 *
 * @property int    $id
 * @property string $name
 * @property string $bti_code
 */
class OdopmArea extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'odopm.odopm_areas';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['name', 'bti_code'], 'string', 'max' => 255],
        ];
    }
}
