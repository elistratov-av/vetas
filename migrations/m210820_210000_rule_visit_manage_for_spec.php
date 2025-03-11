<?php

use app\commands\migrate\Migration;

/**
 * Добавляет разрешение на управление животными и владельцами для ролей vetSpecGos и vetSpecGosAmb
 */
class m210820_210000_rule_visit_manage_for_spec extends \app\common\migrate\RbacMigration
{
    protected $assign = [
        'data.owners.manage.W' => [
            'roles' => [
                'vetSpecGos',
                'vetSpecGosAmb',
            ],
        ],
        'data.pets.manage.W' => [
            'roles' => [
                'vetSpecGos',
                'vetSpecGosAmb',
            ],
        ],
    ];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions($this->assign);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions($this->assign, false);
    }
}
