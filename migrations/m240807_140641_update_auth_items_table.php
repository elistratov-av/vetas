<?php

error_reporting(E_ERROR | E_PARSE); // Отображать только ошибки и парсинг


use app\common\migrate\RbacMigration;

/**
 * Class m240807_140641_update_auth_items_table
 */
class m240807_140641_update_auth_items_table extends RbacMigration
{

    protected $rolesData = [
        [
            'role' => 'shelterViewer',
            'description' => 'Обозреватель приютов',
            'ext_description' => 'роль предназначена для просмотра приютов',
        ]
    ];

    protected $permissions = [
        'shelter.manage.menu' => [
            'roles' => ['shelterViewer']
            ],
            'shelter.pets.manage' => [
            'roles' => ['shelterViewer']
            ],
            'data.ro' => [
            'roles' => ['shelterViewer']
            ]
    ];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addRbacRoles($this->rolesData);
        $this->grantPermissions($this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions($this->permissions);
        $this->removeRbacRoles($this->rolesData);
    }

}
