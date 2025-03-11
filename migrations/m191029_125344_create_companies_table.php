<?php

use yii\db\Migration;

/**
 * Handles the creation of table `companies`.
 */
class m191029_125344_create_companies_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.companies', [
            'id' => $this->primaryKey(),
            'fullname' => $this->string()->notNull(),
            'phone' => $this->string(),
            'email' => $this->string()->notNull(),
            'address' => $this->string(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('companies');
    }
}
