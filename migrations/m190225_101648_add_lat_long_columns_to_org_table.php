<?php

use yii\db\Migration;

/**
 * Handles adding lat_long to table `org`.
 */
class m190225_101648_add_lat_long_columns_to_org_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'latitude', $this->float(8));
        $this->addColumn('organizations', 'longitude', $this->float(8));
        $sql = "
        UPDATE organizations 
        SET latitude = addresses.latitude::float,
            longitude = addresses.longitude::float
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
}
