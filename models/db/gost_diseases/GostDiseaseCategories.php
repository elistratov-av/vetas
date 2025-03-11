<?php

namespace app\models\db\gost_diseases;

use app\common\validators\FullTrimValidator;
use app\models\db\ActiveRecord;

/**
 * @property string $name
 */
class GostDiseaseCategories extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.gost_disease_categories';
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 250],
            ['name', 'unique'],
            [['name'], FullTrimValidator::class],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }
}
