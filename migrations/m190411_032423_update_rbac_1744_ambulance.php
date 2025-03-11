<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190411_032423_update_rbac_1744_ambulance
 */
class m190411_032423_update_rbac_1744_ambulance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = $this->getAuthManager();

        $roleSpec = $auth->getRole(Role::ROLE_VET_SPECIALIST_GOS_AMB);
        $roleDispatcher = $auth->getRole(Role::ROLE_DISPATCHER);

        // удалить ранее выданные разрешения в связи с изменением
        // концепции приемов НВП

        $permissionNames = [
            'data.pets.manage.W',
            'data.owners.manage.W',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($roleSpec, $permission);
        }

        // удалим у диспетчеров разрешения для обычных приемов и расписаний

        $permissionNames = [
            'activity.visits.manage',
            'activity.visits.create',
            'activity.visits.edit',
            'activity.visits.cancel',
            'registry.schedule.manage',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($roleDispatcher, $permission);
        }

        // создадим новые разрешения специально для приемов НВП

        $permissionNames = [
            'ambulance.visits.manage' => 'Учет приемов НВП',
            'ambulance.visits.create' => 'Учет приемов НВП: создание приема',
            'ambulance.visits.edit.new' => 'Управление приемом НВП: редактирование приема в состоянии "Новый", "Изменен", "К переносу"',
            'ambulance.visits.cancel' => 'Управление приемом НВП: отмена приема в состоянии "В работе"',
            'ambulance.visits.manage.menu' => 'Приемы НВП: доступность пункта меню',
        ];

        foreach ($permissionNames as $permissionName => $description) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $description;
            $auth->add($permission);
            $auth->addChild($roleDispatcher, $permission);
            $auth->addChild($roleSpec, $permission);
        }

        $permissionName = 'ambulance.visits.edit';
        $description = 'Управление приемом НВП: редактирование приема';
        $permission = $auth->createPermission($permissionName);
        $permission->description = $description;
        $auth->add($permission);
        $auth->addChild($roleSpec, $permission);

        $permissionName = 'ambulance.schedule.manage';
        $description = 'Управление рабочим графиком НВП: поиск по рабочему графику';
        $permission = $auth->createPermission($permissionName);
        $permission->description = $description;
        $auth->add($permission);
        $auth->addChild($roleDispatcher, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = $this->getAuthManager();

        $roleSpec = $auth->getRole(Role::ROLE_VET_SPECIALIST_GOS_AMB);
        $roleDispatcher = $auth->getRole(Role::ROLE_DISPATCHER);

        $permissionNames = [
            'ambulance.visits.manage',
            'ambulance.visits.create',
            'ambulance.visits.edit.new',
            'ambulance.visits.cancel',
            'ambulance.visits.manage.menu',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($roleDispatcher, $permission);
            $auth->removeChild($roleSpec, $permission);
            $auth->remove($permission);
        }

        $permissionName = 'ambulance.schedule.manage';
        $permission = $auth->getPermission($permissionName);
        $auth->removeChild($roleDispatcher, $permission);
        $auth->remove($permission);
    }

    /**
     * @return \app\common\components\rbac\DbManager
     */
    private function getAuthManager()
    {
        return \Yii::$app->authManager;
    }
}
