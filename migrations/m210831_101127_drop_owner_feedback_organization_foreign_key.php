<?php

use app\commands\migrate\Migration;

/**
 * Class m210831_101127_drop_owner_feedback_organization_foreign_key
 */
class m210831_101127_drop_owner_feedback_organization_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-owner_feedback-id_organization',
            'owner_feedback'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addForeignKey(
            'fk-owner_feedback-id_organization',
            'owner_feedback',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210831_101127_drop_owner_feedback_organization_foreign_key cannot be reverted.\n";

        return false;
    }
    */
}
