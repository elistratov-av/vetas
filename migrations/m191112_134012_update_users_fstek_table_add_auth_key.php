<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m191112_134012_update_users_fstek_table_add_auth_key
 */
class m191112_134012_update_users_fstek_table_add_auth_key extends Migration
{
    private $tableName = 'admin.users_fstek';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'auth_key', $this->string(32));

        $rows = (new Query())
            ->from($this->tableName)
            ->all();

        foreach ($rows as $user) {
            $key = \Yii::$app->security->generateRandomString();
            \Yii::$app->db
                ->createCommand()
                ->update($this->tableName, ['auth_key' => $key], ['id' => $user['id']])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'auth_key');
    }
}
