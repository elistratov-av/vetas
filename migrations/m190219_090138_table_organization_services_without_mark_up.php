<?php

use app\commands\migrate\Migration;

/**
 * Class m190219_090138_table_organization_services_without_mark_up
 */
class m190219_090138_table_organization_services_without_mark_up extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('organization_services_without_mark_up', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->notNull(),
            'id_service' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'organization_services_without_mark_up',
            'Список услуг на которые не распостраняется увеличение стоимости ночью');


        $this->addForeignKey(
            'fk-organization_services_without_mark_up-id_organization',
            'organization_services_without_mark_up',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-organization_services_without_mark_up-id_service',
            'organization_services_without_mark_up',
            'id_service',
            'gov_services',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-organization_services_without_mark_up-id_organization',
            'organization_services_without_mark_up'
        );

        $this->dropForeignKey(
            'fk-organization_services_without_mark_up-id_service',
            'organization_services_without_mark_up'
        );

        $this->dropTable('organization_services_without_mark_up');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190219_090138_table_organization_services_without_mark_up cannot be reverted.\n";

        return false;
    }
    */
}
