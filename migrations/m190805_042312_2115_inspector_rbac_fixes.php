<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190805_042312_2115_inspector_rbac_fixes
 *
 * Предоставление роли "Инспектор" доступа уровня R к функциям:
 * - Учет владельцев животных: поиск
 * - Журналы предоставления услуг. С ограничением доступа к данным: Пользователю роли "Инспектор" доступен только "Журнал регистрации и вакцинации"
 *
 * предоставление роли "Инспектор" доступа к пунктам меню:
 * - Владельцы животных
 * - Поиск специалистов
 */
class m190805_042312_2115_inspector_rbac_fixes extends \app\common\migrate\RbacMigration
{
    private static $permission_data = [
        'data.owners.manage' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
        'data.journals.manage' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
        'data.owners.manage.menu' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
        'data.specialists.manage.menu' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
        'data.specialists.manage' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions(self::$permission_data);

        $rule = new \app\common\components\rbac\rules\InspectorJournalRule();
        $auth = $this->getAuthManager();
        $auth->add($rule);

        $this->db->createCommand()->update(
            'auth_item',
            ['rule_name' => 'InspectorJournalRule'],
            ['name' => 'data.journals.manage']
        )
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions(self::$permission_data);

        $this->db->createCommand()->update(
            'auth_item',
            ['rule_name' => null],
            ['name' => 'data.journals.manage']
        )
            ->execute();

        $rule = new \app\common\components\rbac\rules\InspectorJournalRule();
        $auth = $this->getAuthManager();
        $auth->remove($rule);
    }
}
