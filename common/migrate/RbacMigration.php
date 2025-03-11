<?php


namespace app\common\migrate;

use Yii;
use app\commands\migrate\Migration;
use yii\base\InvalidConfigException;
use yii\helpers\Console;

/**
 * Миграции с правами
 * @package app\common\migration
 */
class RbacMigration extends Migration
{

    /**
     * Добавляет права и назначает указанным ролям
     *
     * Формат $permission_data
     *
     *  'data.vacpoints.manage.menu' => [
     *      'descr' => 'Прививочные пункты: доступность пункта меню',
     *      'rule_name' => 'UserOrgRule',
     *      'roles' => [
     *          'sysAdminGos',
     *           'managementGos',
     *          ...
     *      ],
     *   ]
     *
     * @param $permission_data
     * @throws \yii\base\Exception
     */
    protected function grantPermissions($permission_data)
    {
        $auth = $this->getAuthManager();

        foreach ($permission_data as $permission_name => $data) {

            $permission = $auth->getPermission($permission_name);

            if ($permission === null) {
                $this->info('Create PERMISSION: ' . $permission_name,[Console::BOLD]);

                $description = $data['descr'];
                $permission = $auth->createPermission($permission_name);
                $permission->description = $description;
                if (!empty($data['rule_name'])) {
                    $permission->ruleName = $data['rule_name'];
                }
                $auth->add($permission);
            }
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $role_name) {
                    $this->info('Grant PERMISSION: ' . $permission_name . ' to ' . $role_name);

                    $role = $auth->getRole($role_name);
                    $auth->addChild($role, $permission);
                }
            }else{
                throw new InvalidConfigException('Check array permissions - key role not found');
            }
        }
    }


    /**
     * Отзывает указанные права у указанных ролей
     * При remove_permission удалит и права
     *
     * Формат $permission_data
     *
     *  'data.vacpoints.manage.menu' => [
     *      'descr' => 'Прививочные пункты: доступность пункта меню',
     *      'roles' => [
     *          'sysAdminGos',
     *           'managementGos',
     *          ...
     *      ],
     *   ]
     *
     * @param $permission_data
     * @param bool $remove_permission
     */
    protected function revokePermissions($permission_data, $remove_permission = false)
    {
        $auth = $this->getAuthManager();

        foreach ($permission_data as $permission_name => $data) {
            $permission = $auth->getPermission($permission_name);
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $role_name) {
                    $this->info('Revoke PERMISSION: ' . $permission_name . ' to ' . $role_name);

                    $role = $auth->getRole($role_name);
                    $auth->removeChild($role, $permission);
                }
            }else{
                throw new InvalidConfigException('Check array permissions - key role not found');
            }

            if ($remove_permission == true) {
                $this->info('REMOVE PERMISSION: ' . $permission_name,
                    [Console::BOLD, Console::FG_RED]);

                $auth->remove($permission);
            }
        }
    }

    /**
     * Добавляет роли
     *
     * Пример
     * [
     *  [
     *      'role' => 'sysAdminGos',
     *      'description' => 'Системный администратор (гос)',
     *      'ext_description' => 'роль предназначена для системных администраторов, которые являются специалистами, работающими в «Комитете ветеринарии города Москва»',
     *  ],
     * ];
     *
     * @param $roles_data
     * @throws \Exception
     */
    protected function addRbacRoles($roles_data)
    {
        $auth = $this->getAuthManager();

        foreach ($roles_data as $role_data) {
            $this->info('Create ROLE: ' . $role_data['role'],[Console::BOLD]);

            $role = $auth->createRole($role_data['role']);
            $role->description = $role_data['description'] . "\n" . $role_data['ext_description'];
            $auth->add($role);
        }
    }

    /**
     * Удаляет роли
     *
     * Пример
     * [
     *  [
     *      'role' => 'sysAdminGos',
     *      'description' => 'Системный администратор (гос)',
     *      'ext_description' => 'роль предназначена для системных администраторов, которые являются специалистами, работающими в «Комитете ветеринарии города Москва»',
     *  ],
     * ];
     * @param $roles_data
     */
    protected function removeRbacRoles($roles_data)
    {
        $auth = $this->getAuthManager();

        foreach ($roles_data as $role_data) {
            $this->info('REMOVE ROLE: ' . $role_data['role'],[Console::BOLD, Console::FG_RED]);

            $role = $auth->getRole($role_data['role']);
            $auth->remove($role);
        }
    }

    /**
     * Возвращает authManager
     *
     * @return \yii\rbac\ManagerInterface
     */
    protected function getAuthManager()
    {
        return Yii::$app->authManager;
    }

    /**
     * Выводит сообщение
     *
     * @param $message
     * @param array $format
     */
    protected function info($message, $format = [])
    {
        Console::output(Console::ansiFormat($message, $format));
    }

}
