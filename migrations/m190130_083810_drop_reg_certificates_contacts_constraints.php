<?php

use app\commands\migrate\Migration;

/**
 * Class m190130_083810_drop_reg_certificates_contacts_constraints
 */
class m190130_083810_drop_reg_certificates_contacts_constraints extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('alter table public.reg_certificates drop constraint "fk-reg_certificates-mail"');
        $this->execute('alter table public.reg_certificates drop constraint "fk-reg_certificates-phone"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190130_083810_drop_reg_certificates_contacts_constraints cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190130_083810_drop_reg_certificates_contacts_constraints cannot be reverted.\n";

        return false;
    }
    */
}
