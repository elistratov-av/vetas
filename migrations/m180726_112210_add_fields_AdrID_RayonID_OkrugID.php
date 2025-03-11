<?php

use yii\db\Migration;

/**
 * Class m180726_112210_add_fields_AdrID_RayonID_OkrugID
 */
class m180726_112210_add_fields_AdrID_RayonID_OkrugID extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('addresses', 'AdrID', 'integer');
        $this->addColumn('addresses', 'RayonID', 'integer');
        $this->addColumn('addresses', 'OkrugID', 'integer');
        $this->createIndex('AdrID_unique', 'addresses', 'AdrID', true);
        
        $this->addColumn('districts', 'RayonID', 'integer');
        $this->createIndex('RayonID_unique', 'districts', 'RayonID', true);
        $this->createIndex('district_name', 'districts', 'name');
        
        $this->addColumn('areas', 'OkrugID', 'integer');
        $this->createIndex('OkrugID_unique', 'areas', 'OkrugID', true);
        $this->createIndex('area_name', 'areas', 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('addresses', 'AdrID');
        $this->dropColumn('addresses', 'RayonID');
        $this->dropColumn('addresses', 'OkrugID');
        $this->dropIndex('AdrID_unique', 'addresses');
        
        $this->dropColumn('districts', 'RayonID');
        $this->dropIndex('RayonID_unique', 'districts');
        $this->dropIndex('district_name', 'districts');
        
        $this->dropColumn('areas', 'OkrugID');
        $this->dropIndex('OkrugID_unique', 'areas');
        $this->dropIndex('area_name', 'areas');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180726_112210_add_fields_AdrID_RayonID_OkrugID cannot be reverted.\n";

        return false;
    }
    */
}
