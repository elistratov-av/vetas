<?php

namespace app\models\db;

use app\common\validators\FilterUcwordsValidator;
use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "public.breeds".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $species_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Breeds extends ActiveRecord
{
    // Наименование записи "породы" в случае если порода не указана
    const NOT_SELECTED_NAME = 'Не указана';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.breeds';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], FullTrimValidator::class],
            [['name', 'description'], 'string'],
            [['species_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'species_id'], 'unique', 'targetAttribute' => ['name', 'species_id'], 'message' => 'The combination of Name and Species ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'species_id' => 'Species ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSpecies(){
        return $this->hasOne(Species::class, ['id' => 'species_id']);
    }
}
