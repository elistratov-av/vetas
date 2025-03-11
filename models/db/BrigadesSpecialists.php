<?php

namespace app\models\db;

use app\common\validators\FilterUcwordsValidator;
use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "public.breeds".
 *
 * @property integer $id_brigade
 * @property integer $id_specialist
 * @property integer $created_by
 * @property string $created_at
 */
class BrigadesSpecialists extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.brigades_specialists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['name'], 'required'],
//            [['name'], FullTrimValidator::class],
//            [['name', 'description'], 'string'],
//            [['species_id', 'created_by', 'updated_by'], 'integer'],
//            [['created_at', 'updated_at'], 'safe'],
//            [['name', 'species_id'], 'unique', 'targetAttribute' => ['name', 'species_id'], 'message' => 'The combination of Name and Species ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
//            'id' => 'ID',
//            'name' => 'Name',
//            'description' => 'Description',
//            'species_id' => 'Species ID',
//            'created_by' => 'Created By',
//            'updated_by' => 'Updated By',
//            'created_at' => 'Created At',
//            'updated_at' => 'Updated At',
        ];
    }


}
