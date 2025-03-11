<?php

use app\commands\migrate\Migration;
use app\models\db\Params;
use app\models\db\Reports;
use app\models\db\ReportsParams;
use yii\helpers\Console;

/**
 * Class m190207_014402_update_report_params_add_p2_serialservicenum
 */
class m190207_014402_update_report_params_add_p2_serialservicenum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tech_name = 'P2_SerialServiceNum';

        $param = Params::findOne(['tech_name' => $tech_name]);

        if (!empty($param)) {
            $this->db->createCommand()
                ->delete(ReportsParams::tableName(), ['id_param' => $param->id])
                ->execute();
        } else {
            $param = new Params([
                'name' => 'Экспертиза (исследование) №',
                'tech_name' => $tech_name,
                'datatype' => 'text',
                'datatype_details' => '50',
                'visit_flag' => false,
            ]);
            if (!$param->save()) {
                Console::output('Error saving param ' . $tech_name);

                return false;
            }
        }

        $reportsIds = Reports::find()
            ->select('id')
            ->where(['report_type' => Reports::TYPE_REPORT])
            ->orderBy(['id' => SORT_ASC])
            ->column();

        if (empty($reportsIds)) {
            Console::output('No reports found');
            return;
        }

        $created_at = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($reportsIds as $reportId) {
            $rows[] = [$param->id, $reportId, $created_at];
        }

        $this->db->createCommand()
            ->batchInsert(ReportsParams::tableName(), ['id_param', 'id_report', 'created_at'], $rows)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190207_014402_update_report_params_add_p2_serialservicenum cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190207_014402_update_report_params_add_p2_serialservicenum cannot be reverted.\n";

        return false;
    }
    */
}
