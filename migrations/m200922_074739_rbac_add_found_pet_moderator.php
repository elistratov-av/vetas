<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m200922_074739_rbac_add_found_pet_moderator
 */
class m200922_074739_rbac_add_found_pet_moderator extends RbacMigration
{
    private $rolesData = [
        [
            'role' => Role::ROLE_FOUND_PET_MODERATOR,
            'description' => 'Модератор сервиса "Поиск животных"',
            'ext_description' => 'роль предназначена для модераторов сервиса "Поиск животных"',
        ],
    ];
    private $permissionsData = [
        'found_pet.manage.menu' => [
            'descr' => 'Объявления сервиса "Поиск животных": доступность пункта меню',
            'roles' => [
                Role::ROLE_FOUND_PET_MODERATOR,
            ],
        ],
        'found_pet.manage.W' => [
            'descr' => 'Объявления сервиса "Поиск животных": редактирование',
            'roles' => [
                Role::ROLE_FOUND_PET_MODERATOR,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addRbacRoles($this->rolesData);
        $this->grantPermissions($this->permissionsData);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions($this->permissionsData);
        $this->removeRbacRoles($this->rolesData);
    }
}
