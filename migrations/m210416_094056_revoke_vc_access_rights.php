<?php

use app\common\migrate\RbacMigration;
use yii\base\InvalidConfigException;

/**
 * Class m210416_094056_revoke_vc_access_rights
 */
class m210416_094056_revoke_vc_access_rights extends RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->assign = [
            'data.organizations.vc' => [
                'descr' => 'Просмотр прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                ],
            ],
            'data.organizations.vc.W' => [
                'descr' => 'Настройка прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.organizations.vc.menu' => [
                'descr' => 'Настройка прививочных пунктов - доступность меню',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function safeUp()
    {
        $this->revokePermissions($this->assign, true);
    }

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeDown()
    {
        $this->grantPermissions($this->assign);
    }
}
