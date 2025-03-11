<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m190918_071521_update_users_table_add_auth_key
 */
class m190918_071521_update_users_table_add_auth_key extends Migration
{
    private $tableName = 'users';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'auth_key', $this->string(32)->comment('Ключ для инвалидации токена'));

        $rows = (new Query())
            ->from('{{%' . $this->tableName . '}}')
            ->all();

        foreach ($rows as $user) {
            $key = \Yii::$app->security->generateRandomString();
            \Yii::$app->db
                ->createCommand()
                ->update('{{%' . $this->tableName . '}}', ['auth_key' => $key], ['id' => $user['id']])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%' . $this->tableName . '}}', 'auth_key');
    }
}
