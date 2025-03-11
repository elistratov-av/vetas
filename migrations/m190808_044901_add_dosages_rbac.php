<?php

use app\commands\migrate\Migration;

/**
 * Class m190802_122616_2049_fix_shelter_rbac
 */
class m190808_044901_add_dosages_rbac extends \app\common\migrate\RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->assign = [
            'data.dosages.manage' => [
                'descr' => 'Настройка дозировки препаратов: поиск',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'registryGos',
                    'vetSpecGos'
                ],
            ],
            'data.dosages.manage.W' => [
                'descr' => 'Настройка дозировки препаратов: СUD',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.dosages.calc' => [
                'descr' => 'Калькулятор расчета дозировки препарата',
                'roles' => [
                    'vetSpecGos',
                ],
            ],
        ];
    }

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
        $this->revokePermissions($this->assign, true);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190802_122616_2049_fix_shelter_rbac cannot be reverted.\n";

        return false;
    }
    */
}
