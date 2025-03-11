<?php

use app\commands\migrate\Migration;

/**
 * Class m210211_165315_3228_confirm_payment_rights_for_registry
 */
class m210211_165315_3228_confirm_payment_rights_for_registry extends Migration
{
    private static $permissionData = [
        'activity.visits.manage.confirm-payment' => [
            'descr' => 'Управление приемом: управление состояние оплаты приема (в пределах организации)',
            'roles' => [
                'registryGos',
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


        // Создаем новое правило (если надо) и отдаем его регистратуре
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

        // Отзываем новое
        foreach (self::$permissionData as $permissionName => $data) {
            $permission = $auth->getPermission($permissionName);
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    $role = $auth->getRole($roleName);
                    $auth->removeChild($role, $permission);
                }
            }

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
        echo "m210211_165315_3228_confirm_payment_rights_for_registry cannot be reverted.\n";

        return false;
    }
    */
}
