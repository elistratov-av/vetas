<?php

use yii\db\Migration;

/**
 * Handles the creation of table `params`.
 */
class m180717_190224_create_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('params', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'tech_name' => $this->string(100)->notNull(),
            'datatype' => $this->string(255)->notNull()->comment('numeric, text, dttm, dict'),
            'datatype_details' => $this->string(255),
            'param_config' => $this->json(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('params');
    }
}
