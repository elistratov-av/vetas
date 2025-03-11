<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\rules\AllOrgsCompositeRule5;
/**
 * Class m210125_090116__3228_visit_access_ro_rights_for_managers
 */
class m210125_090116__3228_visit_access_ro_rights_for_managers extends Migration
{


    private static $permissionData = [

        'activity.visits.view' => [
            'descr' => 'Просмотр (только чтение) всех визитов в пределах организационной сети',
            'roles' => [
                'managementGos',
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
        echo "m210119_135703_3228_visit_access_ro_rights cannot be reverted.\n";

        return false;
    }
    */

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210125_090116__3228_visit_access_ro_rights_for_managers cannot be reverted.\n";

        return false;
    }
    */
}
