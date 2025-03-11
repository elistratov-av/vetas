<?php

use app\commands\migrate\Migration;

/**
 * Class m190412_081857_table_descriptions_templates
 */
class m190412_081857_table_descriptions_templates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('descriptions_templates',[
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->notNull(),
            'id_user' => $this->integer()->notNull(),
            'caption' => $this->string()->notNull(),
            'template' => $this->string()->notNull(),
            'public' => $this->boolean()->notNull()->defaultValue('false'),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk_descriptions_templates_users',
            'descriptions_templates',
            'id_user',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_descriptions_templates_organizations',
            'descriptions_templates',
            'id_organization',
            'organizations',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_descriptions_templates_users',
            'descriptions_templates'
        );

        $this->dropForeignKey(
            'fk_descriptions_templates_organizations',
            'descriptions_templates'
        );

        $this->dropTable('descriptions_templates');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190412_081857_table_descriptions_templates cannot be reverted.\n";

        return false;
    }
    */
}
