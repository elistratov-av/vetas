<?php

use app\commands\migrate\Migration;

/**
 * Class m181022_130028_create_mosru_services_admin_view
 */
class m181022_130028_create_mosru_services_admin_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE OR REPLACE VIEW admin.mosru_services_admin AS 
        SELECT mosru_services.id, mosru_services.name, mosru_services_gov_services.at_home
        FROM mosru_services
        LEFT JOIN mosru_services_gov_services 
        ON mosru_services.id = mosru_services_gov_services.id_mosru_service
        group by mosru_services.id, mosru_services.name, mosru_services_gov_services.at_home
        order by mosru_services.name;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW admin.mosru_services_admin;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181022_130028_create_mosru_services_admin_view cannot be reverted.\n";

        return false;
    }
    */
}
