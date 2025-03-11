<?php

use yii\db\Migration;

/**
 * Handles the creation of table `newsletter_reception`.
 */
class m230928_065010_create_newsletter_reception_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('newsletter_reception', [
            'id' => $this->primaryKey(),
            'type' => $this->integer()->notNull()->defaultValue(3),
            'name' => $this->string()->notNull(),
            'name_organizations' => $this->string(),
            'organizations' => $this->integer()->notNull(),
            'text' => $this->text()->notNull(),
            'status' => $this->boolean()->notNull()->defaultValue(true),
            'address' => $this->string()
        ]);

        $this->addForeignKey(
            'fk-newsletter_type-id_newsletter_reception-type',
            'newsletter_reception',
            'type',
            'newsletter_type',
            'id',
            'CASCADE'
        );


        $this->addForeignKey(
            'fk-organizations-id_newsletter_reception-organizations',
            'newsletter_reception',
            'organizations',
            'organizations',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('newsletter_reception');
    }
}
