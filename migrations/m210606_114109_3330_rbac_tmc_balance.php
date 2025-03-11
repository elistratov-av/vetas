<?php

use app\common\components\rbac\rules\BalanceAddRule;

/**
 * Class m210606_114109_3330_rbac_tmc_balance
 */
class m210606_114109_3330_rbac_tmc_balance extends  \app\common\migrate\RbacMigration
{
    protected $assign = [
        'tmc.balance.add' => [
            'descr' => 'Постановка ТМЦ на баланс',
            'rule_name' => BalanceAddRule::class,
            'roles' => [
                'technicMto',
            ],
        ],
        'tmc.balance.list' => [
            'descr' => 'Просмотр баланса ТМЦ',
            'roles' => [
                'technicMto',
                'sysAdminGos',
                'managementGos',
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
        $this->revokePermissions($this->assign, true);
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210606_114109_3330_rbac_tmc_balance cannot be reverted.\n";

        return false;
    }
    */
}
