<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use yii\helpers\Console;

/**
 * Class m191120_142955_2544_fix_uzi_screening_params
 */
class m191120_142955_2544_fix_uzi_screening_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $param = Params::findOne(['tech_name' => 'P0_Abdomenorgansystem']);

        if ($param === null) {
            Console::output('Param not found');
            return true;
        }

        $service = GovServices::findOne(['name' => 'Ультразвуковой скрининг органов брюшной полости']);

        if ($service === null) {
            Console::output('Service not found');
            return true;
        }

        $this->delete(
            GovServicesParams::tableName(),
            [
                'id_param' => $param->id,
                'id_service' => $service->id,
            ]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191120_142955_2544_fix_uzi_screening_params cannot be reverted.\n";

        return false;
    }
}
