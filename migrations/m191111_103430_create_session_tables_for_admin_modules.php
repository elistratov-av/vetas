<?php

use app\commands\migrate\Migration;

/**
 * Class m191111_103430_create_session_tables_for_admin_modules
 */
class m191111_103430_create_session_tables_for_admin_modules extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ([
                     'public.sessions_vetadmin' => 'public.users',
                     'admin.sessions_admin' => 'admin.users_fstek',
                 ] as $tableName => $fkTable) {
            $this->createTable($tableName, [
                'id' => $this->string()->notNull(),
                'expire' => $this->integer(),
                'data' => $this->binary(),
                'id_user' => $this->integer(),
                'valid_until' => $this->dateTime(0),
                'last_active_at' => $this->dateTime(0),
                'ip' => $this->string(),
                'ua' => $this->string(1000),
                'ua_hash' => $this->string(),
                'created_at' => $this->dateTime(0),
                'updated_at' => $this->dateTime(0),
            ]);

            $tn = str_replace('.', '_', $tableName);

            $this->createIndex(
                'idx_' . $tn . '_id',
                $tableName,
                'id',
                true
            );

            $this->createIndex(
                'idx_' . $tn . '_id_user',
                $tableName,
                'id_user'
            );

            $this->addForeignKey(
                'fk_' . $tn . '_id_user',
                $tableName,
                'id_user',
                $fkTable,
                'id',
                'CASCADE'
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.sessions_vetadmin');
        $this->dropTable('admin.sessions_admin');
    }
}
