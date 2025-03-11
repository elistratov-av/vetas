<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;

/**
 * Class OdopmReferences
 * @package app\models\db\odopm
 *
 * @property int    $id
 * @property int    $id_reference
 * @property int    $id_value
 * @property string $name
 */
class OdopmReferences extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'odopm.references';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_reference', 'id_value'], 'integer'],
            ['name', 'string', 'max' => 255],
        ];
    }
}
