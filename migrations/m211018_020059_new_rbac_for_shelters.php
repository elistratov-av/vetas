<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m211018_020059_new_rbac_for_shelters
 */
class m211018_020059_new_rbac_for_shelters extends RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rolesToAdd = [
            Role::ROLE_SHELTER_ACTIVITY_ADMIN => [
                'role' => Role::ROLE_SHELTER_ACTIVITY_ADMIN,
                'description' => 'Администратор деятельности подчиненных приютов',
                'ext_description' => 'Возможность просмотра и редактирования сведений о животных по всем городским приютам.',
            ],
            Role::ROLE_SHELTER_VETERINARIAN => [
                'role' => Role::ROLE_SHELTER_VETERINARIAN,
                'description' => 'Ветеринарный врач приюта',
                'ext_description' => 'Доступ к созданию, редактированию карточек учета животного в городских приютах (конкретный приют)',
            ],
            Role::ROLE_SHELTER_FAUNA_MONITORING_SPEC => [
                'role' => Role::ROLE_SHELTER_FAUNA_MONITORING_SPEC,
                'description' => 'Специалист по мониторингу и учету городской фауны',
                'ext_description' => 'Возможность просмотра и редактирования сведений о животных по всем городским приютам',
            ],
            Role::ROLE_SHELTER_ANIMAL_SOCIALIZATION_SPEC => [
                'role' => Role::ROLE_SHELTER_ANIMAL_SOCIALIZATION_SPEC,
                'description' => 'Специалист по социализации животных приюта',
                'ext_description' => 'Доступ к созданию, редактированию карточек учета животного в городских приютах (конкретный приют)',
            ],
            Role::ROLE_SHELTER_SYS_ADMIN => [
                'role' => Role::ROLE_SHELTER_SYS_ADMIN,
                'description' => 'Системный администратор (приюты)',
                'ext_description' => 'Роль «Системный администратор (приюты)» должна предусматривать возможность просмотра и редактирования сведений о животных по всем приютам.',
            ],
        ];

        $this->addRbacRoles($rolesToAdd);

        $roles = array_keys($rolesToAdd);

        $permissionData = [
            'data.classificators.species' => [
                'descr' => 'Справочник видов животных: поиск',
                'roles' => $roles,
            ],
            'data.classificators.breeds' => [
                'descr' => 'Справочник пород животных: поиск',
                'roles' => $roles,
            ],
            'data.faqs.manage.menu' => [
                'descr' => 'Справочная информация: доступность пункта меню',
                'roles' => $roles,
            ],
            'shelter.manage.menu' => [
                'descr' => 'Животные приюта: доступность пункта меню',
                'roles' => $roles,
            ],
            'shelter.owners.manage' => [
                'descr' => 'Учет владельцев животных (приюты): поиск',
                'roles' => $roles,
            ],
            'shelter.owners.manage.W' => [
                'descr' => 'Учет владельцев животных (приюты): CUD',
                'roles' => [
                    Role::ROLE_SHELTER_ACTIVITY_ADMIN,
                ],
            ],
            'shelter.pets.manage' => [
                'descr' => 'Учет животных (приюты): поиск животных',
                'roles' => $roles,
            ],
            'shelter.pets.manage.W' => [
                'descr' => 'Учет животных (приюты): управление животным: CUD',
                'roles' => [
                    Role::ROLE_SHELTER_ACTIVITY_ADMIN,
                ],
            ],
        ];

        $this->grantPermissions($permissionData);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211020_020059_new_rbac_for_shelters cannot be reverted.\n";

        return false;
    }

}
