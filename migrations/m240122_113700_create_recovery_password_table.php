<?php

use app\models\db\Pets;
use yii\db\Migration;

class m240122_113700_create_recovery_password_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('recovery_password', [
            'id' => $this->primaryKey(),
            'status' => $this->tinyInteger(1)->notNull(),
            'login' => $this->string(50)->notNull(),
            'date' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable('recovery_password', 'Таблица запросов на восстановление пароля');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('recovery_password');
    }
}
