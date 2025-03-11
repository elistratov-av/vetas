<?php

use app\common\migrate\RbacMigration;
use yii\base\InvalidConfigException;

/**
 * Class m210416_094356_add_vc_access_rights
 */
class m210416_094356_add_vc_access_rights extends RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->assign = [
            'data.vaccinationStation.R' => [
                'descr' => 'Просмотр прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                ],
            ],
            'data.vaccinationStation.W' => [
                'descr' => 'Настройка прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.vaccinationStation.menu' => [
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
     * @throws InvalidConfigException
     */
    public function safeDown()
    {
        $this->revokePermissions($this->assign, true);
    }
}
