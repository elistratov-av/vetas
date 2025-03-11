<?php

use app\commands\migrate\Migration;

/**
 * Class m190805_014427_update_change_requests_table_created_updated
 */
class m190805_014427_update_change_requests_table_created_updated extends Migration
{
    private $tableName = 'change_request';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%' . $this->tableName . '}}', 'id_request', 'id');
        $this->execute('alter sequence if exists change_request_id_request_seq rename to change_request_id_seq');

        $this->dropColumn('{{%' . $this->tableName . '}}', 'create_date');
        $this->dropColumn('{{%' . $this->tableName . '}}', 'up_date');

        $this->addColumn('{{%' . $this->tableName . '}}', 'created_at', $this->dateTime(0));
        $this->addColumn('{{%' . $this->tableName . '}}', 'updated_at', $this->dateTime(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190805_014427_update_change_requests_table_created_updated cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190805_014427_update_change_requests_table_created_updated cannot be reverted.\n";

        return false;
    }
    */
}
