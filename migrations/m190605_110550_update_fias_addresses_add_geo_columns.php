<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_110550_update_fias_addresses_add_geo_columns
 */
class m190605_110550_update_fias_addresses_add_geo_columns extends Migration
{
    private $tableName = 'fias_addresses';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'lat', $this->string());
        $this->addColumn('{{%' . $this->tableName . '}}', 'lon', $this->string());
        $this->addColumn('{{%' . $this->tableName . '}}', 'coords', 'geometry');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%' . $this->tableName . '}}', 'lat');
        $this->dropColumn('{{%' . $this->tableName . '}}', 'lon');
        $this->dropColumn('{{%' . $this->tableName . '}}', 'coords');
    }
}
