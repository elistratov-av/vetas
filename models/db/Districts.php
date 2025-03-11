<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "public.districts".
 *
 * @property integer $id
 * @property string $name
 * @property string $bti_code
 * @property integer $id_area
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $RayonID
 * @property string $oktmo
 */
class Districts extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.districts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'bti_code', 'id_area'], 'required'],
            [['id_area', 'created_by', 'updated_by', 'RayonID'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['oktmo', 'bti_code'], 'string', 'max' => 255],
            [['RayonID'], 'unique'],
            [['oktmo'], 'string'],
            [['id_area'], 'exist', 'skipOnError' => true, 'targetClass' => Areas::class, 'targetAttribute' => ['id_area' => 'id']],
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
            'bti_code' => 'Bti Code',
            'id_area' => 'Id Area',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'RayonID' => 'Rayon ID',
            'oktmo' => 'OKTMO',
        ];
    }
}
