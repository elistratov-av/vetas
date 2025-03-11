<?php

use app\commands\migrate\Migration;
use app\models\db\Params;
use app\models\db\GovServicesParams;
use app\models\db\VisitServiceParamValues;
use yii\helpers\Console;

/**
 * Class m190129_085038_1363_update_params
 */
class m190129_085038_1363_update_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $param = Params::findOne(['tech_name' => 'P65_Urindisorddepositionvalue']);

        if ($param === null) {
            Console::output('Param not found');
        } else {
            $this->db->createCommand()
                ->delete(VisitServiceParamValues::tableName(), ['id_param' => $param->id])
                ->execute();
            $this->db->createCommand()
                ->delete(GovServicesParams::tableName(), ['id_param' => $param->id])
                ->execute();
            $this->db->createCommand()
                ->delete('public.reports_params', ['id_param' => $param->id])
                ->execute();
            $param->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190129_085038_1363_update_params cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190129_085038_1363_update_params cannot be reverted.\n";

        return false;
    }
    */
}
