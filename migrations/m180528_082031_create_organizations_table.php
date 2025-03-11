<?php

use yii\db\Migration;

/**
 * Handles the creation of table `organizations`.
 */
class m180528_082031_create_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('organizations',[
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer(),
            'id_org_type' => $this->integer(),
            'name' => $this->string(255)->notNull(),
            'short_name' => $this->string(255),
            'inn' => $this->string(255),
            'kpp' => $this->string(255),
            'ogrn' => $this->string(255),
          ]
        );

        $this->createIndex('id', 'organizations', 'id', true);

        $this->addForeignKey('fk-organizations-id_org_type',  'organizations', 'id_org_type', 'org_types', 'id_org_type', 'NO ACTION' );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('organizations');
    }
}
