<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\rules\UserOrgRule;

/**
 * Class m210122_123749_3228_confirm_payment_rights_fix
 */
class m210122_123749_3228_confirm_payment_rights_fix extends Migration
{

    private static $permissionData = [
        'activity.visits.manage.confirm-payment' => [
            'descr' => 'Управление приемом: управление состояние оплаты приема (в пределах организации)',
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

        // Изменяем старое правило. Теперь действует только на свой прием
        $permission = $auth->getPermission('activity.visits.confirm-payment');
        $permission->description = 'Управление приемом: оплата своего приема';
        $permission->ruleName = 'VisitSpecialistRule';
        $auth->update('activity.visits.confirm-payment', $permission);


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

        // Возвращаем старое правило
        $permission = $auth->getPermission('activity.visits.confirm-payment');
        $permission->description = 'Управление приемом: управление состояние оплаты приема';
        $permission->ruleName = 'UserOrgRule';
        $auth->update('activity.visits.confirm-payment', $permission);

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

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210122_123749_3228_confirm_payment_rights_fix cannot be reverted.\n";

        return false;
    }
    */
}
