<?php

use app\common\migrate\RbacMigration;

/**
 * Class m210317_094056_add_vc_access_rights
 */
class m210317_094056_add_vc_access_rights extends RbacMigration
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
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->grantPermissions($this->assign);
    }

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function safeDown()
    {
        $this->revokePermissions($this->assign, true);
    }
}
