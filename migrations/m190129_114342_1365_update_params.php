<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use yii\helpers\Console;

/**
 * Class m190129_114342_1365_update_params
 */
class m190129_114342_1365_update_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $param = Params::findOne(['tech_name' => 'P13_Serviceresultdesc']);
        if ($param === null) {
            Console::output('Param not found');
        } else {
            $service = GovServices::findOne(['name' => 'Ультразвуковое исследование']);
            if ($service === null) {
                Console::output('Service not found');
            } else {
                $rel = GovServicesParams::findOne([
                    'id_param' => $param->id,
                    'id_service' => $service->id,
                ]);
                if ($rel === null) {
                    $this->db->createCommand()
                        ->insert(GovServicesParams::tableName(), [
                            'id_param' => $param->id,
                            'id_service' => $service->id,
                            'req_in' => false,
                            'req_out' => false,
                            'sort_by' => 998,
                        ])
                        ->execute();
                } else {
                    Console::output('Relation already exists');
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190129_114342_1365_update_params cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190129_114342_1365_update_params cannot be reverted.\n";

        return false;
    }
    */
}
