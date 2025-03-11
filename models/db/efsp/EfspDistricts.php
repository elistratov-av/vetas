<?php

namespace app\models\db\efsp;

use Yii;

/**
 * This is the model class for table "efsp.districts".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $level
 * @property string $name
 * @property string $type
 * @property string $okato
 * @property string $oktmo
 * @property string $code
 * @property string $bti_city_area_code
 */
class EfspDistricts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'efsp.districts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'level'], 'default', 'value' => null],
            [['parent_id', 'level'], 'integer'],
            [['level'], 'required'],
            [['name', 'type', 'okato', 'oktmo', 'code', 'bti_city_area_code'], 'string', 'max' => 256],
            [['bti_city_area_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'level' => 'Level',
            'name' => 'Name',
            'type' => 'Type',
            'okato' => 'Okato',
            'oktmo' => 'Oktmo',
            'code' => 'Code',
            'bti_city_area_code' => 'Bti City Area Code',
        ];
    }
}
