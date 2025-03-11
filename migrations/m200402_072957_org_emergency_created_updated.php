<?php

use app\commands\migrate\Migration;

/**
 * Class m200402_072957_org_emergency_created_updated
 */
class m200402_072957_org_emergency_created_updated extends Migration
{
    protected $table = 'organizations_emergency';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn($this->table, 'created_by', 'integer');
        $this->addColumn($this->table, 'updated_by', 'integer');
        $this->addColumn($this->table, 'created_at', 'timestamp');
        $this->addColumn($this->table, 'updated_at', 'timestamp');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->table, 'created_by');
        $this->dropColumn($this->table, 'updated_by');
        $this->dropColumn($this->table, 'created_at');
        $this->dropColumn($this->table, 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200402_072957_org_emergency_created_updated cannot be reverted.\n";

        return false;
    }
    */
}
