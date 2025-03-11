<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "public.addresses".
 *
 * @property integer $id
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property integer $id_area
 * @property integer $id_district
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $AdrID
 * @property integer $RayonID
 * @property integer $OkrugID
 * @property string $name_tsv
 */
class Addresses extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.addresses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'latitude', 'longitude'], 'required'],
            [['id_area', 'id_district', 'created_by', 'updated_by', 'AdrID', 'RayonID', 'OkrugID'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name_tsv'], 'string'],
            [['name', 'latitude', 'longitude'], 'string', 'max' => 255],
            [['AdrID'], 'unique'],
            [['id_area'], 'exist', 'skipOnError' => true, 'targetClass' => Areas::className(), 'targetAttribute' => ['id_area' => 'id']],
            [['id_district'], 'exist', 'skipOnError' => true, 'targetClass' => Districts::className(), 'targetAttribute' => ['id_district' => 'id']],
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
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'id_area' => 'Id Area',
            'id_district' => 'Id District',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'AdrID' => 'Adr ID',
            'RayonID' => 'Rayon ID',
            'OkrugID' => 'Okrug ID',
            'name_tsv' => 'Name Tsv',
        ];
    }
}
