<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tmc_types`.
 */
class m180529_120131_create_tmc_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tmc_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'tmc_class' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('tmc_types');
    }
}
