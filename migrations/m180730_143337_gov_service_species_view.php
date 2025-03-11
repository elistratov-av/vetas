<?php

use yii\db\Migration;

/**
 * Class m180730_143337_gov_service_species_view
 */
class m180730_143337_gov_service_species_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE VIEW gov_services_species AS SELECT * FROM species_services');
        $this->dropColumn('gov_services', 'id_species');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180730_143337_gov_service_species_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180730_143337_gov_service_species_view cannot be reverted.\n";

        return false;
    }
    */
}
