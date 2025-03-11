<?php

use app\commands\migrate\Migration;

/**
 * Class m190321_011149_update_rbac_rules_and_permissions
 */
class m190321_011149_update_rbac_rules_and_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;
        $rule = new \app\common\components\rbac\rules\VisitCompositeRule2();
        $auth->add($rule);

        $this->db->createCommand()->update(
            'auth_item',
            [
                'rule_name' => 'VisitCompositeRule2',
                'description' => 'Управление приемом: редактирование приема'
            ],
            ['name' => 'activity.visits.edit']
        )
            ->execute();

        $this->db->createCommand()->update(
            'auth_item',
            [
                'rule_name' => 'VisitCompositeRule2',
            ],
            ['name' => 'activity.visits.cancel']
        )
            ->execute();

        $permissionName = 'activity.visits.edit.new';
        $description = 'Управление приемом: редактирование приема в состоянии "Новый", "Изменен", "К переносу"';
        $ruleName = 'VisitCompositeRule1';

        $permission = $auth->createPermission($permissionName);
        $permission->description = $description;
        $permission->ruleName = $ruleName;
        $auth->add($permission);

        $roleNames = [
            'registryGos',
            'vetSpecGos',
        ];

        foreach ($roleNames as $roleName) {
            $role = $auth->getRole($roleName);
            $auth->addChild($role, $permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = \Yii::$app->authManager;

        $this->db->createCommand()->update(
            'auth_item',
            [
                'rule_name' => 'VisitCompositeRule1',
                'description' => 'Управление приемом: редактирование приема в состоянии "Новый", "Изменен", "К переносу"'
            ],
            ['name' => 'activity.visits.edit']
        )
            ->execute();

        $this->db->createCommand()->update(
            'auth_item',
            [
                'rule_name' => 'VisitSpecialistRule',
            ],
            ['name' => 'activity.visits.cancel']
        )
            ->execute();

        $permissionName = 'activity.visits.edit.new';
        $permission = $auth->getPermission($permissionName);

        $roleNames = [
            'registryGos',
            'vetSpecGos',
        ];

        foreach ($roleNames as $roleName) {
            $role = $auth->getRole($roleName);
            $auth->removeChild($role, $permission);
        }

        $auth->remove($permission);

        $rule = new \app\common\components\rbac\rules\VisitCompositeRule2();
        $auth->remove($rule);
    }
}
