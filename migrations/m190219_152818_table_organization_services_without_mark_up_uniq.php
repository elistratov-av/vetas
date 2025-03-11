<?php

use app\commands\migrate\Migration;

/**
 * Class m190219_152818_table_organization_services_without_mark_up_uniq
 */
class m190219_152818_table_organization_services_without_mark_up_uniq extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Delete not uniq
        $this->execute("
DELETE   FROM organization_services_without_mark_up T1
  USING       organization_services_without_mark_up T2
WHERE  T1.ctid    < T2.ctid       -- delete the \"older\" ones
  AND  T1.id_organization    = T2.id_organization
  AND  T1.id_service = T2.id_service;
        ");

        $this->createIndex(
            'uniq_organization_services_without_mark_up_id_organization_id_service',
            'organization_services_without_mark_up',
            [
                'id_organization',
                'id_service'
            ],
            true
            );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'uniq_organization_services_without_mark_up_id_organization_id_service',
            'organization_services_without_mark_up'
        );

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190219_152818_table_organization_services_without_mark_up_uniq cannot be reverted.\n";

        return false;
    }
    */
}
