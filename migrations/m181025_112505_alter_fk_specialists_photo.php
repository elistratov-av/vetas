<?php

use app\commands\migrate\Migration;

/**
 * Class m181025_112505_alter_fk_specialists_photo
 */
class m181025_112505_alter_fk_specialists_photo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-specialists-photo', 'specialists');
        $this->addForeignKey('fk-specialists-photo', 'specialists', 'photo', 'files', 'id', 'SET NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-specialists-photo', 'specialists');
        $this->addForeignKey('fk-specialists-photo', 'specialists', 'photo', 'files', 'id', 'CASCADE');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181025_112505_alter_fk_specialists_photo cannot be reverted.\n";

        return false;
    }
    */
}
