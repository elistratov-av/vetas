<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m190702_144642_2049_shelter_rbac
 */
class m190702_144642_2049_shelter_rbac extends RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rolesToRemove = [
            [
                'role' => 'trapping',
            ],
        ];

        $this->removeRbacRoles($rolesToRemove);

        $rolesToAdd = [
            [
                'role' => Role::ROLE_SHELTER_MANAGEMENT,
                'description' => 'Администрация приюта',
                'ext_description' => 'роль предназначена для пользователей, которые будут управлять учетными записями приюта',
            ],
            [
                'role' => Role::ROLE_SHELTER_SPECIALIST,
                'description' => 'Специалист приюта',
                'ext_description' => 'роль предназначена для пользователей, которые будут управлять животными приюта',
            ],
        ];

        $this->addRbacRoles($rolesToAdd);

        $permissionData = [
            'data.classificators.species' => [
                'descr' => 'Справочник видов животных: поиск',
                'roles' => [
                    Role::ROLE_SHELTER_MANAGEMENT,
                    Role::ROLE_SHELTER_SPECIALIST,
                ],
            ],
            'data.classificators.breeds' => [
                'descr' => 'Справочник пород животных: поиск',
                'roles' => [
                    Role::ROLE_SHELTER_MANAGEMENT,
                    Role::ROLE_SHELTER_SPECIALIST,
                ],
            ],
            'data.faqs.manage.menu' => [
                'descr' => 'Справочная информация: доступность пункта меню',
                'roles' => [
                    Role::ROLE_SHELTER_MANAGEMENT,
                    Role::ROLE_SHELTER_SPECIALIST,
                ],
            ],
            'shelter.manage.menu' => [
                'descr' => 'Животные приюта: доступность пункта меню',
                'roles' => [
                    Role::ROLE_SHELTER_MANAGEMENT,
                    Role::ROLE_SHELTER_SPECIALIST,
                ],
            ],
            'shelter.owners.manage' => [
                'descr' => 'Учет владельцев животных (приюты): поиск',
                'roles' => [
                    Role::ROLE_SHELTER_MANAGEMENT,
                    Role::ROLE_SHELTER_SPECIALIST,
                ],
            ],
            'shelter.owners.manage.W' => [
                'descr' => 'Учет владельцев животных (приюты): CUD',
                'roles' => [
                    Role::ROLE_SHELTER_SPECIALIST,
                ],
            ],
            'shelter.pets.manage' => [
                'descr' => 'Учет животных (приюты): поиск животных',
                'roles' => [
                    Role::ROLE_SHELTER_MANAGEMENT,
                    Role::ROLE_SHELTER_SPECIALIST,
                ],
            ],
            'shelter.pets.manage.W' => [
                'descr' => 'Учет животных (приюты): управление животным: CUD',
                'roles' => [
                    Role::ROLE_SHELTER_SPECIALIST,
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
        echo "m190702_144642_2049_shelter_rbac cannot be reverted.\n";

        return false;
    }
}
