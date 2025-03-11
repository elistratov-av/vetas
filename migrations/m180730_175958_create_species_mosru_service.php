<?php

use yii\db\Migration;

/**
 * Class m180730_175958_create_species_mosru_service
 */
class m180730_175958_create_species_mosru_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "
CREATE VIEW species_mosru_service AS
SELECT 
	msgs.id_mosru_service AS id_mosru_service,
	species_services.id_species AS id_species
FROM 
	species_services
INNER JOIN
	mosru_services_gov_services AS msgs ON species_services.id_service = msgs.id_gov_services 
ORDER BY 
	msgs.id_mosru_service";

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute(" DROP VIEW public.species_mosru_service;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180730_175958_create_species_mosru_service cannot be reverted.\n";

        return false;
    }
    */
}
