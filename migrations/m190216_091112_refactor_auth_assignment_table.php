<?php

use app\commands\migrate\Migration;

/**
 * Class m190216_091112_refactor_auth_assignment_table
 */
class m190216_091112_refactor_auth_assignment_table extends Migration
{
    private $tableName = 'auth_assignment';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('auth_assignment_user_id_idx', '{{%' . $this->tableName . '}}');
        $this->dropColumn('{{%' . $this->tableName . '}}', 'user_id');

        $columns = [
            'id_user',
            'id_specialist',
        ];

        foreach ($columns as $column) {
            $this->addColumn('{{%' . $this->tableName . '}}', $column, $this->integer());
            $this->createIndex('auth_assignment_' . $column . '_idx', '{{%' . $this->tableName . '}}', $column);
        }
        $this->createIndex('auth_assignment_' . implode('_', $columns) . '_idx', '{{%' . $this->tableName . '}}', $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'user_id', $this->string(64));
        $this->createIndex('auth_assignment_user_id_idx', '{{%' . $this->tableName . '}}', 'user_id');

        $columns = [
            'id_user',
            'id_specialist',
        ];

        $this->dropIndex('auth_assignment_' . implode('_', $columns) . '_idx', '{{%' . $this->tableName . '}}');
        foreach ($columns as $column) {
            $this->dropIndex('auth_assignment_' . $column . '_idx', '{{%' . $this->tableName . '}}');
            $this->dropColumn('{{%' . $this->tableName . '}}', $column);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190216_091122_new_rbac_roles cannot be reverted.\n";

        return false;
    }
    */
}
