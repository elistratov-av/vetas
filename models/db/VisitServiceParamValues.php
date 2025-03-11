<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "public.visit_service_param_values".
 *
 * @property integer $id
 * @property double $num_value
 * @property string $char_value
 * @property integer $date_value
 * @property integer $dict_value
 * @property integer $id_visit
 * @property integer $id_visitservice
 * @property integer $id_visit_service_tmc
 * @property integer $id_pet
 * @property integer $id_param
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $complex_value
 */
class VisitServiceParamValues extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.visit_service_param_values';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['num_value'], 'number'],
            [['char_value', 'complex_value'], 'string'],
            [['date_value', 'dict_value', 'id_visit', 'id_visitservice', 'id_visit_service_tmc', 'id_pet', 'id_param', 'created_by', 'updated_by'], 'integer'],
            [['id_visitservice', 'id_param'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_param'], 'exist', 'skipOnError' => true, 'targetClass' => Params::className(), 'targetAttribute' => ['id_param' => 'id']],
            [['id_visitservice'], 'exist', 'skipOnError' => true, 'targetClass' => VisitsGovServices::className(), 'targetAttribute' => ['id_visitservice' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                   => 'ID',
            'num_value'            => 'Num Value',
            'char_value'           => 'Char Value',
            'date_value'           => 'Date Value',
            'dict_value'           => 'Dict Value',
            'id_visit'             => 'Id Visit',
            'id_visitservice'      => 'Id Visitservice',
            'id_visit_service_tmc' => 'Id Visitservice TMC',
            'id_pet'               => 'Id Pet',
            'id_param'             => 'Id Param',
            'created_by'           => 'Created By',
            'updated_by'           => 'Updated By',
            'created_at'           => 'Created At',
            'updated_at'           => 'Updated At',
            'complex_value'        => 'Complex Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsGovServices()
    {
        return $this->hasOne(VisitsGovServices::class, ['id' => 'id_visitservice']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGovServicesParams()
    {
        return $this->hasOne(GovServicesParams::class, ['id_service' => 'id_service'])
            ->viaTable(VisitsGovServices::tableName(), ['id' => 'id_visitservice']);
    }
}
