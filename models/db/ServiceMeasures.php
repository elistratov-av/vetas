<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "public.service_measures".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $count_flag
 */
class ServiceMeasures extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.service_measures';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['count_flag'], 'boolean'],
            [['name', 'description'], 'string', 'max' => 255],
            [['name', 'description'], FullTrimValidator::class],
            [['name'], 'unique'],
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
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'count_flag' => 'Count Flag',
        ];
    }
}
