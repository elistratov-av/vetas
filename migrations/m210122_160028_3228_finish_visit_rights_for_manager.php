<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\rules\UserOrgRule;

/**
 * Class m210122_160028_3228_finish_visit_rights_for_manager
 */
class m210122_160028_3228_finish_visit_rights_for_manager extends Migration
{
    private static $permissionData = [
        'activity.visits.manage.finish' => [
            'descr' => 'Управление приемом: завершение приема (для менеджеров в пределах организации)',
            'roles' => [
                'managementGos',
            ],
            'rule_name' => UserOrgRule::class,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Создаем новое правило и отдаем его менеджменту
        foreach (self::$permissionData as $permissionName => $data) {
            $description = $data['descr'];
            $permission = $auth->getPermission($permissionName);
            $rule_name = empty($data['rule_name']) ? null : $data['rule_name'];
            if ($permission === null) {
                $permission = $auth->createPermission($permissionName);
                $permission->description = $description;
                $permission->ruleName = $rule_name;
                $auth->add($permission);
            }
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    $role = $auth->getRole($roleName);
                    $auth->addChild($role, $permission);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // Удаляем новое
        foreach (self::$permissionData as $permissionName => $data) {
            $permission = $auth->getPermission($permissionName);
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    $role = $auth->getRole($roleName);
                    $auth->removeChild($role, $permission);
                }
            }
            $auth->remove($permission);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210122_160028_3228_finish_visit_rights_for_manager cannot be reverted.\n";

        return false;
    }
    */
}
