<?php

namespace app\models\db\gost_diseases;

use app\common\validators\FullTrimValidator;
use app\models\db\ActiveRecord;

/**
 * @property string                $name
 * @property int                   $id_category
 * @property GostDiseaseCategories $gostDiseaseCategory
 */
class GostDiseaseSubCategories extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.gost_disease_sub_categories';
    }

    public function rules()
    {
        return [
            [['name', 'id_category'], 'required'],
            ['name', 'string', 'max' => 250],
            [['name'], 'unique'],
            [['name'], FullTrimValidator::class],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
            [
                ['id_category'],
                'exist',
                'skipOnError' => true,
                'targetClass' => GostDiseaseCategories::class,
                'targetAttribute' => ['id_category' => 'id'],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGostDiseaseCategory()
    {
        return $this->hasOne(GostDiseaseCategories::class, ['id' => 'id_category']);
    }
}
