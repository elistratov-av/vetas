<?php

use yii\db\Migration;

/**
 * Class m180823_161821_viev_etp_visits_mosru_services
 */
class m180823_161821_viev_etp_visits_mosru_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "
CREATE VIEW 
	etp.visits_mosru_services AS 
SELECT
	visits_gov_services.id_visit,
	mosru_services.id,
	mosru_services.name
FROM
	visits_gov_services
 LEFT JOIN 
	\"mosru_services_gov_services\" ON mosru_services_gov_services.id_gov_services = visits_gov_services.id_service
LEFT JOIN
	mosru_services ON mosru_services.id = mosru_services_gov_services.id_mosru_service
WHERE 
	mosru_services_gov_services.id_gov_services IS NOT NULL
        ";
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP VIEW etp.visits_mosru_services;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180823_161821_viev_etp_visits_mosru_services cannot be reverted.\n";

        return false;
    }
    */
}
