<?php

use app\commands\migrate\Migration;

/**
 * Class m210809_062950_add_owner_and_pet_permissions_to_inspectors
 */
class m210809_062950_add_owner_and_pet_permissions_to_inspectors extends Migration
{
    private static $accessData = [
        'data.pets.manage.W',
        'data.owners.manage.W',
    ];

    private const INSPECTOR_ROLE = \app\common\components\rbac\Role::ROLE_INSPECTOR;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;
        $role = $auth->getRole($this::INSPECTOR_ROLE);

        foreach(self::$accessData as $access) {
            $permission = $auth->getPermission($access);
            $auth->addChild($role, $permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = \Yii::$app->authManager;
        $role = $auth->getRole($this::INSPECTOR_ROLE);

        foreach(self::$accessData as $access) {
            $permission = $auth->getPermission($access);
            $auth->removeChild($role, $permission);
        }
    }
}
