<?php

namespace app\models\db;

/**
 * This is the model class for table "public.gov_services_reports".
 *
 * @property integer $id
 * @property integer $id_report
 * @property integer $id_service
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class GovServicesReports extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.gov_services_reports';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_report', 'id_service'], 'required'],
            [['id_report', 'id_service', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_service'], 'exist', 'skipOnError' => true, 'targetClass' => GovServices::class, 'targetAttribute' => ['id_service' => 'id']],
            [['id_report'], 'exist', 'skipOnError' => true, 'targetClass' => Reports::class, 'targetAttribute' => ['id_report' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_report' => 'Id Report',
            'id_service' => 'Id Service',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
