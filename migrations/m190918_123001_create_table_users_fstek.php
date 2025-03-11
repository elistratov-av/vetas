<?php

use app\commands\migrate\Migration;
use app\models\db\admin\AdminUser;

/**
 * Class m190918_123001_create_table_users_fstek
 */
class m190918_123001_create_table_users_fstek extends Migration
{
    private $tableName = 'admin.users_fstek';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'login' => $this->string(),
            'role' => $this->smallInteger()->notNull(),
            //'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'password' => $this->string(),
            'last_login' => $this->dateTime(0),
            'ip' => $this->string(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
            'created_by' =>  $this->integer(),
            'updated_by' => $this->integer(),
            'email' => $this->string(),
            'is_temp_password' => $this->boolean()->defaultValue(false)->comment('Временный пароль?'),
            'password_valid_till' =>  $this->dateTime(0)->comment('Срок действия пароля'),
            'password_valid_till_min' => $this->dateTime(0)->comment('Минимальный срок действия пароля'),
            'f_fio'=> $this->string(),
            'i_fio'=> $this->string(),
            'o_fio'=> $this->string(),
            'is_blocked' => $this->boolean()->defaultValue(false),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(false)->comment('Флаг: пользователь удален'),
        ]);

        $tn = str_replace('.', '_', $this->tableName);

        $this->createIndex(
            'idx_' . $tn . 'is_blocked',
            $this->tableName,
            'is_blocked'
        );

        $this->createIndex(
            'idx_' . $tn . '_role',
            $this->tableName,
            'role'
        );

        // $model = new AdminUser([
        //     'login' => 'admin',
        //     'role' => AdminUser::ROLE_ADMIN,
        //     //'status' => AdminUser::STATUS_NOT_BLOCKED,
        //     'f_fio' => 'admin',
        //     'i_fio' => 'admin',
        //     'email' => 'test@test.com',
        // ]);
        // $model->setPassword('123456');
        // if (!$model->save()) {
        //     $errors = $model->getErrorSummary(true);
        //     throw new \Exception(empty($errors) ? 'Ошибка при создании пользователя' : implode("\n", array_values($errors)));
        // }

        // $model = new AdminUser([
        //     'login' => 'security',
        //     'role' => AdminUser::ROLE_SECURITY,
        //     //'status' => AdminUser::STATUS_NOT_BLOCKED,
        //     'f_fio' => 'security',
        //     'i_fio' => 'security',
        //     'email' => 'test1@test.com',
        // ]);
        // $model->setPassword('123456');
        // if (!$model->save()) {
        //     $errors = $model->getErrorSummary(true);
        //     throw new \Exception(empty($errors) ? 'Ошибка при создании пользователя' : implode("\n", array_values($errors)));
        // }

        // $model = new AdminUser([
        //     'login' => 'manager',
        //     'role' => AdminUser::ROLE_ACCOUNTS_MANAGER,
        //     //'status' => AdminUser::STATUS_NOT_BLOCKED,
        //     'f_fio' => 'manager',
        //     'i_fio' => 'manager',
        //     'email' => 'test2@test.com',
        // ]);
        // $model->setPassword('123456');
        // if (!$model->save()) {
        //     $errors = $model->getErrorSummary(true);
        //     throw new \Exception(empty($errors) ? 'Ошибка при создании пользователя' : implode("\n", array_values($errors)));
        // }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
