<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "reports_params".
 *
 * @property int    $id
 * @property int    $id_param
 * @property int    $id_report
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property \app\models\db\Params $param
 * @property \app\models\db\Reports $report
 */
class ReportsParams extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.reports_params';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_param', 'id_report'], 'required'],
            [['id_param', 'id_report'], 'integer'],
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
    public function getReport()
    {
        return $this->hasOne(Reports::class, ['id' => 'id_report']);
    }
}
