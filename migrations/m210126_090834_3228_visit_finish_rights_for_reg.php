<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\rules\UserOrgRule;

/**
 * Class m210126_090834_3228_visit_finish_rights_for_reg
 */
class m210126_090834_3228_visit_finish_rights_for_reg extends Migration
{
    private static $permissionData = [
        'activity.visits.manage.finish' => [
            'descr' => 'Управление приемом: завершение приема (для менеджеров в пределах организации)',
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
        $auth = \Yii::$app->authManager;

        foreach (self::$permissionData as $permissionName => $data) {
            $permission = $auth->getPermission($permissionName);

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
        $auth = \Yii::$app->authManager;

        foreach (self::$permissionData as $permissionName => $data) {
            $permission = $auth->getPermission($permissionName);
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    $role = $auth->getRole($roleName);
                    $auth->removeChild($role, $permission);
                }
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210126_090834_3228_visit_finish_rights_for_reg cannot be reverted.\n";

        return false;
    }
    */
}
