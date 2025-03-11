<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "public.gov_services_params".
 *
 * @property integer $id
 * @property integer $id_param
 * @property integer $id_service
 * @property boolean $flag_in
 * @property boolean $flag_out
 * @property boolean $req_in
 * @property boolean $req_out
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $sort_by
 * @property Params $param
 *
 * @property GovServices $service
 */
class GovServicesParams extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.gov_services_params';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_param', 'id_service'], 'required'],
            [['id_param', 'id_service', 'created_by', 'updated_by', 'sort_by'], 'integer'],
            [['req_in', 'req_out'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_service'], 'exist', 'skipOnError' => true, 'targetClass' => GovServices::class, 'targetAttribute' => ['id_service' => 'id']],
            [['id_param'], 'exist', 'skipOnError' => true, 'targetClass' => Params::class, 'targetAttribute' => ['id_param' => 'id']],
            [['flag_in', 'flag_out'], 'default', 'value' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_param' => 'Id Param',
            'id_service' => 'Id Service',
            'req_in' => 'Req In',
            'req_out' => 'Req Out',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'sort_by' => 'Sort By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParam()
    {
        return $this->hasOne(Params::class, ['id' => 'id_param']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDictionaries()
    {
        return $this->hasMany(Dictionaries::class, ['type' => 'datatype_details'])->viaTable('params', ['id' => 'id_param']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(GovServices::class, ['id' => 'id_service']);
    }
}
