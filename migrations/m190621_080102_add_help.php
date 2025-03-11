<?php

use app\commands\migrate\Migration;

/**
 * Class m190621_080102_add_help
 */
class m190621_080102_add_help extends Migration
{
    private static $permissionData = [
        // Help
        'data.help.manage.menu' => [
            'descr' => 'Справочная информация: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
                'vetSpecGosAmb',
                'dispatcher',
                'inspector',

                'sysAdminPrivFull',
                'managementPrivFull',
                'managementPrivMin',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
            ],
        ],
        'data.help.manage' => [
            'descr' => 'Справочная информация: получение данных',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
                'vetSpecGosAmb',
                'dispatcher',
                'inspector',

                'sysAdminPrivFull',
                'managementPrivFull',
                'managementPrivMin',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
            ],
        ],
        'data.help.manage.W' => [
            'descr' => 'Справочная информация: управление:CUD',
            'roles' => [
                'sysAdminGos',
                'managementGos',
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('help', [
            'id' => $this->primaryKey(),
            'caption' => $this->text()->notNull(),
            'text' => $this->text()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable('help', 'Справочная информация для сотрудников');

        $this->createTable('help_links', [
            'id' => $this->primaryKey(),
            'href' => $this->string(),
            'text' => $this->string()->notNull(),
            'id_help' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
            'target_blank' => $this->boolean()->notNull()->defaultValue(false),
        ]);

        $this->addCommentOnTable('help', 'Справочная информация для сотрудников - ссылки');

        $this->addForeignKey(
            'fk_help_links-id_help',
            'help_links',
            'id_help',
            'help',
            'id',
            'CASCADE');

        $auth = \Yii::$app->authManager;

        foreach (self::$permissionData as $permissionName => $data) {
            $description = $data['descr'];
            $permission = $auth->getPermission($permissionName);
            if ($permission === null) {
                $permission = $auth->createPermission($permissionName);
                $permission->description = $description;
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
        $this->dropTable('help_links');
        $this->dropTable('help');

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
        echo "m190621_080102_add_help cannot be reverted.\n";

        return false;
    }
    */
}
