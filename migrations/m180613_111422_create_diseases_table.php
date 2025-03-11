<?php

use yii\db\Migration;

/**
 * Handles the creation of table `diseases`.
 */
class m180613_111422_create_diseases_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('diseases', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
            'flag_danger' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('diseases');
    }
}
