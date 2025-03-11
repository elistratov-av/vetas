<?php

use app\commands\migrate\Migration;

/**
 * Class m190821_105629_create_table_signed_visits
 */
class m190821_105629_create_table_signed_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('signed_visits',[
            'id' => $this->primaryKey(),
            'id_visit' => $this->integer(),
            'sign_hash' => $this->string(20000),
            'document' => $this->string(20000),
            'created_at' => $this->dateTime()
        ]);

        $this->addForeignKey('visit_sign_fk', 'signed_visits', 'id_visit', 'visits', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('visit_sign_fk', 'signed_visits');
        $this->dropTable('signed_visits');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190821_105629_create_table_signed_visits cannot be reverted.\n";

        return false;
    }
    */
}
