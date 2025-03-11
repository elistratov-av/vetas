<?php

use app\commands\migrate\Migration;

/**
 * Class m181016_084741_alter_number_type_reg_cert
 */
class m181016_084741_alter_number_type_reg_cert extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('reg_certificates', 'number', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('reg_certificates', 'number', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181016_084741_alter_number_type_reg_cert cannot be reverted.\n";

        return false;
    }
    */
}
