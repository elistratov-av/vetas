<?php

use app\commands\migrate\Migration;

/**
 * Разрешает роли technicMto управление ТМЦ
 */
class m210820_230000_permit_technicMto_manage_tmc extends \app\common\migrate\RbacMigration
{
    protected $assign = [
        'data.dosages.manage' => [
            'roles' => [
                'technicMto',
            ],
        ],
        'data.dosages.manage.W' => [
            'roles' => [
                'technicMto',
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
