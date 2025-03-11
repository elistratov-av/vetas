<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\rules\AllOrgsCompositeRule5;
/**
 * Class m210125_094827__3228_visit_access_ro_rights_for_reg
 */
class m210125_094827__3228_visit_access_ro_rights_for_reg extends Migration
{
    private static $permissionData = [

        'activity.visits.view' => [
            'descr' => 'Просмотр (только чтение) всех визитов в пределах организационной сети',
            'roles' => [
                'registryGos',
            ],
            'rule_name' => AllOrgsCompositeRule5::class,
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
        echo "m210125_094827__3228_visit_access_ro_rights_for_reg cannot be reverted.\n";

        return false;
    }
    */
}
