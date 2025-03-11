<?php

namespace app\models\db;

/**
 * Class VisitServiceReportSerialservicenum
 * @package app\models\db
 *
 * @property int    $id
 * @property int    $year
 * @property int    $id_organization
 * @property int    $id_report
 * @property int    $last_number
 * @property string $created_at
 * @property string $updated_at
 */
class VisitServiceReportSerialservicenum extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.visit_service_report_serialservicenum';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'id_organization', 'id_report', 'last_number'], 'required'],
            [['year', 'id_organization', 'id_report', 'last_number'], 'integer'],
        ];
    }
}
