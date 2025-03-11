<?php

use yii\db\Migration;

/**
 * Handles the creation of table `organizations_emergency`.
 */
class m181108_115416_create_organizations_emergency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('organizations_emergency', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->comment('Ссылка на организацию'),
            'date_from' => $this->dateTime()->comment('Время начала экстренного случая'),
            'date_to' => $this->dateTime()->comment('Время окончания экстренного случая'),
            'reason' => $this->text()->comment('Причина')
        ]);

        $this->addForeignKey(
            'fk-organizations_emergency-id_organization',
            'organizations_emergency',
            'id_organization',
            'organizations',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('organizations_emergency');
    }
}
