<?php

use yii\db\Migration;

/**
 * Handles the creation of table `orgType`.
 */
class m180528_080553_create_orgType_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            'org_types',
            [
              'id_org_type' => $this->primaryKey(),
              'name' => $this->string(50)->notNull(),
              'description' => $this->string(255),
            ]
          );
  
          $this->createIndex('id_org_type', 'org_types', 'id_org_type', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('orgType');
    }
}
