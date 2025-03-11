<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * Class SpecialistsUpdateReasons
 * @package app\models\db
 *
 * @property integer $id
 * @property string  $description
 * @property int     $id_specialist
 * @property int     $created_by
 * @property int     $updated_by
 * @property string  $created_at
 * @property string  $updated_at
 */
class SpecialistsUpdateReasons extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.specialists_update_reasons';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_specialist', 'description'], 'required'],
            ['id_specialist', 'integer'],
            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'filter', 'filter' => 'strip_tags'],
            ['description', 'string', 'max' => 255],
            [['description'], FullTrimValidator::class],
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'safe'],
        ];
    }
}
