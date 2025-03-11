<?php

use app\commands\migrate\Migration;

/**
 * Class m210702_150901_rule_quarantine_manage_quarantine_for_spec
 */
class m210702_150901_rule_quarantine_manage_quarantine_for_spec extends \app\common\migrate\RbacMigration
{
    protected $assign = [
        'quarantine.manage.quarantine.R' => [
            'roles' => [
                'vetSpecGos',
                'vetSpecGosAmb',
            ],
        ],
        'quarantine.manage.animals.R' => [
            'roles' => [
                'vetSpecGos',
                'vetSpecGosAmb',
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
        $this->revokePermissions($this->assign, false);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210702_150901_rule_quarantine_manage_quarantine_for_spec cannot be reverted.\n";

        return false;
    }
    */
}
