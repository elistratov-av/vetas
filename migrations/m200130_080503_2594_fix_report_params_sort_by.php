<?php

use app\commands\migrate\Migration;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use yii\db\Query;

/**
 * Class m200130_080503_2594_fix_report_params_sort_by
 */
class m200130_080503_2594_fix_report_params_sort_by extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            GovServicesParams::tableName(),
            ['sort_by' => 12],
            [
                '=',
                'id_param',
                (new Query())
                    ->select('id')
                    ->from(Params::tableName())
                    ->where(['=', 'tech_name', 'P25_Matkanovoobrazov'])
            ]
        );

        $this->update(
            GovServicesParams::tableName(),
            ['sort_by' => 2],
            [
                '=',
                'id_param',
                (new Query())
                    ->select('id')
                    ->from(Params::tableName())
                    ->where(['=', 'tech_name', 'P16_Capsula'])
            ]
        );
        $this->update(
            GovServicesParams::tableName(),
            ['sort_by' => 10],
            [
                '=',
                'id_param',
                (new Query())
                    ->select('id')
                    ->from(Params::tableName())
                    ->where(['=', 'tech_name', 'P24_Zhelchpuzyrraspoloshenie'])
            ]
        );
        $this->update(
            GovServicesParams::tableName(),
            ['sort_by' => 26],
            [
                '=',
                'id_param',
                (new Query())
                    ->select('id')
                    ->from(Params::tableName())
                    ->where(['=', 'tech_name', 'P42_Podzhelzhelezavisualization'])
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200130_080503_2594_fix_report_params_sort_by cannot be reverted.\n";

        return false;
    }
}
