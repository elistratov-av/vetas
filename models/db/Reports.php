<?php

namespace app\models\db;

/**
 * Class Reports
 * @package app\models\db
 *
 * @property int    $id
 * @property string $name
 * @property string $report_type
 * @property bool   $grouped
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property bool   $sending
 */
class Reports extends ActiveRecord
{
    const TYPE_REPORT = 'R';
    const TYPE_JOURNAL = 'J';

    const CATEGORY_VACCINATION = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.reports';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'report_type'], 'required'],
            ['name', 'string', 'max' => 255],
            ['report_type', 'string', 'length' => 1],
            ['grouped', 'default', 'value' => false],
            ['grouped', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
        ];
    }

}
