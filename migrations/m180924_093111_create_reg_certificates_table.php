<?php

use yii\db\Migration;

/**
 * Handles the creation of table `RegCertificates`.
 */
class m180924_093111_create_reg_certificates_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reg_certificates', [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'number' => $this->integer()->notNull(),
            'id_pet' => $this->integer()->notNull(),
            'id_owner' => $this->integer()->notNull(),
            'phone' => $this->integer()->notNull(),
            'mail' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),

        ]);

        $this->createIndex('idx-reg_certificates-date', 'reg_certificates', 'date');
        $this->createIndex('idx-reg_certificates-number', 'reg_certificates', 'number', true);
        $this->createIndex('idx-reg_certificates-id_pet', 'reg_certificates', 'id_pet');
        $this->createIndex('idx-reg_certificates-id_owner', 'reg_certificates', 'id_owner');
        $this->createIndex('idx-reg_certificates-phone', 'reg_certificates', 'phone');
        $this->createIndex('idx-reg_certificates-mail', 'reg_certificates', 'mail');

        $this->addForeignKey('fk-reg_certificates-pet', 'reg_certificates', 'id_pet', 'pets', 'id');
        $this->addForeignKey('fk-reg_certificates-owner', 'reg_certificates', 'id_owner', 'pet_owners', 'id');
        $this->addForeignKey('fk-reg_certificates-phone', 'reg_certificates', 'phone', 'contacts', 'id');
        $this->addForeignKey('fk-reg_certificates-mail', 'reg_certificates', 'mail', 'contacts', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('reg_certificates');
    }
}
