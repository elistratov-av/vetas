<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shelter_rejection_reason`.
 */
class m210723_061849_create_shelter_rejection_reason_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('shelter_vaccine_rejection', [
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull()->comment('Животное по которому оформлен отказ'),
            'id_organization' => $this->integer()->notNull()->comment('Приют в рамках которого оформлен отказ'),
            'description' => $this->string()->notNull()->comment('Причина отказа от вакцинации'),
            'created_at' => $this->dateTime()->notNull()->comment('Дата отказа'),
            'updated_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull()->comment('Автор отказа'),
            'updated_by' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-shelter_vaccine_rejection-id_organization',
            'shelter_vaccine_rejection',
            'id_organization',
            'organizations',
            'id'
        );

        $this->addForeignKey(
            'fk-shelter_vaccine_rejection-id_pet',
            'shelter_vaccine_rejection',
            'id_pet',
            'pets',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('shelter_vaccine_rejection');
    }
}
