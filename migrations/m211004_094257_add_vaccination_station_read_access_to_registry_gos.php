<?php

use app\commands\migrate\Migration;

/**
 * Class m211004_094257_add_vaccination_station_read_access_to_registry_gos
 */
class m211004_094257_add_vaccination_station_read_access_to_registry_gos extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('registryGos');
        $permission = $auth->getPermission('data.vaccinationStation.R');

        $auth->addChild($role, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('registryGos');
        $permission = $auth->getPermission('data.vaccinationStation.R');

        $auth->removeChild($role, $permission);
    }
}
