<?php

use app\commands\migrate\Migration;

/**
 * Class m210325_063952_3296_categories_access_rights
 */
class m210325_063952_3296_categories_access_rights extends \app\common\migrate\RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->assign = [
            'data.classificators.categories' => [
                'descr' => 'Просмотр категорий ТМЦ',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'registryGos',
                    'vetSpecGos'
                ],
            ],
            'data.classificators.categories.menu' => [
                'descr' => 'Настройка категорий ТМЦ - доступность меню',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.classificators.categories.W' => [
                'descr' => 'Настройка категорий ТМЦ',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.pricelist.categories.menu' => [
                'descr' => 'Настройка категорий для услуг - доступность меню',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ]
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
        echo "m210325_063952_3296_categories_access_rights cannot be reverted.\n";

        return false;
    }
    */
}
