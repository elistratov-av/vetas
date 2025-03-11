<?php

namespace app\models\db\gost_diseases;

use app\common\validators\FullTrimValidator;
use app\models\db\ActiveRecord;

/**
 * @property string                     $name
 * @property string                     $gost_code
 * @property int                        $id_sub_category
 * @property GostDiseaseSubCategories   $gostDiseaseSubCategory
 */
class GostDiseases extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.gost_diseases';
    }

    public function rules()
    {
        return [
            [['name', 'id_sub_category', 'gost_code'], 'required'],
            ['gost_code', 'string', 'max' => 20],
            ['name', 'string', 'max' => 250],
            [['gost_code'], 'unique'],
            [['name'], FullTrimValidator::class],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
            [
                ['id_sub_category'],
                'exist',
                'skipOnError' => true,
                'targetClass' => GostDiseaseSubCategories::class,
                'targetAttribute' => ['id_sub_category' => 'id'],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGostDiseaseSubCategory()
    {
        return $this->hasOne(GostDiseaseSubCategories::class, ['id' => 'id_sub_category']);
    }
}
