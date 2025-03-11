<?php

use app\commands\migrate\Migration;

/**
 * Class m231002_103257_add_gost_diseases_access_rules
 */
class m231002_103257_add_gost_diseases_access_rules extends \app\common\migrate\RbacMigration
{
    protected $assign = [
        'data.classificators.gost-diseases' => [
            'descr' => 'Доступ к просмотру справочника болезней ГОСТ',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecPrivFull',
                'vetSpecGos',
            ],
        ],
        'data.classificators.gost-diseases.W' => [
            'descr' => 'Доступ к редактированию справочника болезней ГОСТ',
            'roles' => [
                'sysAdminGos',
            ],
        ],
        'data.classificators.gost-diseases.menu' => [
            'descr' => 'Доступ к справочнику болезней ГОСТ в боковом меню Ветас',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecPrivFull',
                'vetSpecGos',
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
        $this->revokePermissions($this->assign);
    }
}
