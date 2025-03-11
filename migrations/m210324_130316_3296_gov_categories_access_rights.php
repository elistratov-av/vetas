<?php

use app\commands\migrate\Migration;

/**
 * Class m210324_130316_3296_gov_categories_access_rights
 */
class m210324_130316_3296_gov_categories_access_rights extends \app\common\migrate\RbacMigration
{
    private $assign;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->assign = [
            'data.pricelist.categories' => [
                'descr' => 'Просмотр категорий для услуг',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'registryGos',
                    'vetSpecGos'
                ],
            ],
            'data.pricelist.categories.W' => [
                'descr' => 'Настройка категорий для услуг',
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
        echo "m210324_130316_3296_gov_categories_access_rights cannot be reverted.\n";

        return false;
    }
    */
}
