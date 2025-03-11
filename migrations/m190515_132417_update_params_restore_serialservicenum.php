<?php

use app\commands\migrate\Migration;
use app\models\db\Params;
use app\models\db\Reports;
use app\models\db\ReportsParams;
use yii\helpers\Console;

/**
 * Class m190515_132417_update_params_restore_serialservicenum
 */
class m190515_132417_update_params_restore_serialservicenum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // восстановление параметра P2_SerialServiceNum, удаленного миграцией m190419_084937_params_1740_update_params
        // (был удален на dev-контурах, так как на тот момент этот параметр отсутствовал в файле от аналитиков)
        // на проде данный параметр должен сохраниться на момент релиза 1.2.5

        $tech_name = 'P2_SerialServiceNum';

        $param = Params::findOne(['tech_name' => $tech_name]);

        if (!empty($param)) {
            return;
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

                return;
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
        echo "m190515_132417_update_params_restore_serialservicenum cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190515_132417_update_params_restore_serialservicenum cannot be reverted.\n";

        return false;
    }
    */
}
