<?php


/**
 * Class m190809_191003_add_dosages_menu_rbac
 */
class m190809_191003_add_dosages_menu_rbac extends  \app\common\migrate\RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->assign = [
            'data.doasges.tab.menu' => [
                'descr' => 'Настройка дозировки препаратов: доступность вкладки',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'registryGos',
                    'vetSpecGos'
                ],
            ],
            'data.doasges.add.menu' => [
                'descr' => 'Настройка дозировки препаратов: доступность кнопки Добавить',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.doasges.calculate.menu' => [
                'descr' => 'Калькулятор расчета дозировки препарата: доступность кнопки Расчитать',
                'roles' => [
                    'vetSpecGos'
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
        echo "m190809_191003_add_dosages_menu_rbac cannot be reverted.\n";

        return false;
    }
    */
}
