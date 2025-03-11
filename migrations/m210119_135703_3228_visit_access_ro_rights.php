<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\rules\AllOrgsCompositeRule5;
/**
 * Class m210119_135703_3228_visit_access_ro_rights
 */
class m210119_135703_3228_visit_access_ro_rights extends Migration
{


    private static $permissionData = [
        'activity.visits.view' => [
            'descr' => 'Просмотр (только чтение) всех визитов в пределах организационной сети',
            'roles' => [
                'vetSpecGos',
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
        $auth = \Yii::$app->authManager;

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
        echo "m210119_135703_3228_visit_access_ro_rights cannot be reverted.\n";

        return false;
    }
    */
}
