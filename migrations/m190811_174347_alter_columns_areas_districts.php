<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_174347_alter_columns_areas_districts
 */
class m190811_174347_alter_columns_areas_districts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('areas', 'bti_code', $this->string());
        $this->alterColumn('districts', 'bti_code', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('areas', 'bti_code', $this->integer());
        $this->alterColumn('districts', 'bti_code', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190811_174347_alter_columns_areas_districts cannot be reverted.\n";

        return false;
    }
    */
}
