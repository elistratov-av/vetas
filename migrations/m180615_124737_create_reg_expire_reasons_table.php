<?php

use yii\db\Migration;

/**
 * Handles the creation of table `reg_expire_reasons`.
 */
class m180615_124737_create_reg_expire_reasons_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reg_expire_reasons', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
            'description' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('reg_expire_reasons');
    }
}
