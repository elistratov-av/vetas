<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_121537_fix_lat_long_columns_in_org_table
 */
class m190311_121537_fix_lat_long_columns_in_org_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organizations', 'latitude');
        $this->dropColumn('organizations', 'longitude');

        $this->addColumn('organizations', 'latitude', $this->string(255));
        $this->addColumn('organizations', 'longitude', $this->string(255));
        $sql = "
        UPDATE organizations 
        SET latitude = addresses.latitude,
            longitude = addresses.longitude
        FROM addresses
        WHERE organizations.id_address = addresses.id";
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'latitude');
        $this->dropColumn('organizations', 'longitude');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190311_121537_fix_lat_long_columns_in_org_table cannot be reverted.\n";

        return false;
    }
    */
}
