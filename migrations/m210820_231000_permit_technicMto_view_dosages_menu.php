<?php

use app\commands\migrate\Migration;

/**
 * Разрешает роли technicMto доступ к меню управления дозировками (и возможно ещё что-то...)
 */
class m210820_231000_permit_technicMto_view_dosages_menu extends \app\common\migrate\RbacMigration
{
    protected $assign = [
        'data.dosages.tab.menu' => [
            'roles' => [
                'technicMto',
            ],
        ],
        'data.dosages.add.menu' => [
            'roles' => [
                'technicMto',
            ],
        ],
        'data.dosages.calculate.menu' => [
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
