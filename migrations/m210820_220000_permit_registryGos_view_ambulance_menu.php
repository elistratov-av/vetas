<?php

use app\commands\migrate\Migration;

/**
 * Разрешает ролям registryGos и vetSpecGosAmb доступ к меню vaccination-stations
 */
class m210820_220000_permit_registryGos_view_ambulance_menu extends \app\common\migrate\RbacMigration
{
    protected $assign = [
        'data.vaccinationStation.menu' => [
            'roles' => [
                'registryGos',
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
