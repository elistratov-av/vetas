<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_141648_update_focuses_localities_add_geo_indexes
 */
class m190605_141648_update_focuses_localities_add_geo_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (['quarantines_focuses', 'quarantines_localities'] as $tableName) {
            $this->execute('CREATE INDEX "idx_' . $tableName . '_coords_gist" ON ' . $tableName . ' USING GIST (coords);');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['quarantines_focuses', 'quarantines_localities'] as $tableName) {
            $this->dropIndex('idx_' . $tableName . '_coords_gist', '{{%' . $tableName . '}}');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190605_141648_update_focuses_localities_add_geo_indexes cannot be reverted.\n";

        return false;
    }
    */
}
