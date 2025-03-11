<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "public.params".
 *
 * @property integer $id
 * @property string $name
 * @property string $tech_name
 * @property string $datatype
 * @property string $datatype_details
 * @property string|array $config
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $visit_flag
 */
class Params extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.params';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'tech_name', 'datatype'], 'required'],
            [['config'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['visit_flag'], 'boolean'],
            [['name', 'datatype', 'datatype_details'], 'string', 'max' => 255],
            [['tech_name'], 'string', 'max' => 100],
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
            'tech_name' => 'Tech Name',
            'datatype' => 'Datatype',
            'datatype_details' => 'Datatype Details',
            'config' => 'Config',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'visit_flag' => 'Visit Flag',
        ];
    }
}
