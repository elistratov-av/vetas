<?php

use app\commands\migrate\Migration;

/**
 * Class m190124_060408_update_reg_certificates_mail_not_required
 */
class m190124_060408_update_reg_certificates_mail_not_required extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db
            ->createCommand('alter table "public"."reg_certificates" alter "mail" drop not null')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190124_060408_update_reg_certificates_mail_not_required cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190124_060408_update_reg_certificates_mail_not_required cannot be reverted.\n";

        return false;
    }
    */
}
