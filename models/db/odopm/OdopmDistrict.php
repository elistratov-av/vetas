<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;

/**
 * Class OdopmDistrict
 * @package app\models\db\odopm
 *
 * @property int    $id
 * @property string $name
 * @property string $bti_code
 * @property string $area_bti_code
 * @property string $comment
 */
class OdopmDistrict extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'odopm.odopm_districts';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['name', 'bti_code', 'area_bti_code'], 'string', 'max' => 255],
            ['comment', 'string'],
        ];
    }
}
