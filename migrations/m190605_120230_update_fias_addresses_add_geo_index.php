<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_120230_update_fias_addresses_add_geo_index
 */
class m190605_120230_update_fias_addresses_add_geo_index extends Migration
{
    private $tableName = 'fias_addresses';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // need first CREATE EXTENSION btree_gist;

        $this->execute('CREATE INDEX "idx_' . $this->tableName . '_coords_gist" ON ' . $this->tableName . ' USING GIST (coords);');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_' . $this->tableName . '_coords_gist', '{{%' . $this->tableName . '}}');
    }
}
