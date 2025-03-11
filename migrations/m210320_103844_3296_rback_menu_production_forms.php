<?php

use app\commands\migrate\Migration;

/**
 * Class m210320_103844_3296_rback_menu_production_forms
 */
class m210320_103844_3296_rback_menu_production_forms extends \app\common\migrate\RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->assign = [
            'data.production_form.tab.menu' => [
                'descr' => 'Настройка форм выпуска препаратов/вакцин: доступность вкладки',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'registryGos',
                    'vetSpecGos'
                ],
            ],
            'data.production_form.edit.menu' => [
                'descr' => 'Настройка форм выпуска препаратов/вакцин: доступность кнопки Добавить/удалить',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
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
        echo "m210320_103844_3296_rback_menu_production_forms cannot be reverted.\n";

        return false;
    }
    */
}
