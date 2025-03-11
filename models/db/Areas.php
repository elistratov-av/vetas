<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "public.areas".
 *
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property double $area
 * @property integer $bti_code
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $OkrugID
 */
class Areas extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.areas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'short_name', 'area', 'bti_code'], 'required'],
            [['area'], 'number'],
            [['created_by', 'updated_by', 'OkrugID'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'short_name', 'bti_code'], 'string', 'max' => 255],
            [['OkrugID'], 'unique'],
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
            'short_name' => 'Short Name',
            'area' => 'Area',
            'bti_code' => 'Bti Code',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'OkrugID' => 'Okrug ID',
        ];
    }
}
