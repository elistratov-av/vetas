<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190423_053632_update_rbac_1744_ambulance_3
 */
class m190423_053632_update_rbac_1744_ambulance_3 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = $this->getAuthManager();

        $roleSpec = $auth->getRole(Role::ROLE_VET_SPECIALIST_GOS_AMB);

        $permissionNames = [
            'ambulance.visits.start' => 'Управление приемом НВП: взятие приема в работу',
            'ambulance.visits.finish' => 'Управление приемом НВП: завершение приема',
            'ambulance.visits.confirm-payment' => 'Управление приемом НВП: управление состояние оплаты приема',
        ];

        foreach ($permissionNames as $permissionName => $description) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $description;
            $auth->add($permission);
            $auth->addChild($roleSpec, $permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = $this->getAuthManager();

        $roleSpec = $auth->getRole(Role::ROLE_VET_SPECIALIST_GOS_AMB);

        $permissionNames = [
            'ambulance.visits.start',
            'ambulance.visits.finish',
            'ambulance.visits.confirm-payment',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($roleSpec, $permission);
            $auth->remove($permission);
        }
    }

    /**
     * @return \app\common\components\rbac\DbManager
     */
    private function getAuthManager()
    {
        return \Yii::$app->authManager;
    }
}
