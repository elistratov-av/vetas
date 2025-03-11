<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m200616_133311_new_rbac_permissions
 */
class m200616_133311_new_rbac_permissions extends RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rolesToAdd = [
            [
                'role' => Role::ROLE_INSPECTOR_READONLY,
                'description' => 'Инспектор (режим просмотра)',
                'ext_description' => 'инспектор с аналогичными доступами, но только в режиме чтения: запись, редактирование и удаление запрещены',
            ],
        ];

        $this->addRbacRoles($rolesToAdd);


        $permissionData = [
            'data.pets.manage.menu' => [
                'descr' => 'Поиск животных: доступность пункта меню',
                'roles' => [
                    Role::ROLE_VET_SPECIALIST_GOS,
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.pets.manage' => [
                'descr' => 'Учет животных: поиск животных',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'quarantine.manage.menu' => [
                'descr' => 'Карантин: доступность пункта меню',
                'roles' => [
                    Role::ROLE_SYSADMIN_GOS,
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.owners.manage.menu' => [
                'descr' => 'Владельцы животных: доступность пункта меню',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.classificators.diseases.menu' => [
                'descr' => 'Заболевания: доступность пункта меню',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.classificators.species' => [
                'descr' => 'Справочник видов животных: поиск',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.classificators.breeds' => [
                'descr' => 'Справочник пород животных: поиск',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.owners.manage' => [
                'descr' => 'Учет владельцев животных: поиск',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.gosvetnadzor.manage.menu' => [
                'descr' => 'Госветнадзор: доступность пункта меню',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.gosvetnadzor.violation_admin_rights' => [
                'descr' => 'Справочник АПН: поиск',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.faqs.manage.menu' => [
                'descr' => 'Справочная информация: доступность пункта меню',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.journals.manage.menu' => [
                'descr' => 'Журналы: доступность пункта меню',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.faqs.manage' => [
                'descr' => 'Справочная информация: получение данных',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.gosvetnadzor.violation_type' => [
                'descr' => 'Справочник тип нарушения: поиск',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.gosvetnadzor.violation_admin_rights.menu' => [
                'descr' => 'Справочник АПН: поиск',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.help.manage.menu' => [
                'descr' => 'Справочная информация: доступность пункта меню',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.help.manage' => [
                'descr' => 'Справочная информация: получение данных',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
                ],
            ],
            'data.journals.manage' => [
                'descr' => 'Журналы: получение данных журналов',
                'roles' => [
                    Role::ROLE_INSPECTOR_READONLY,
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
        echo "m200616_133311_new_rbac_permissions cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200616_133311_new_rbac_permissions cannot be reverted.\n";

        return false;
    }
    */
}
