<?php

use app\commands\migrate\Migration;

/**
 * Class m231010_063354_refactor_shift_type_invalid_intersections
 */
class m231010_063354_refactor_shift_type_invalid_intersections extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('shift_type_invalid_intersections', 'shift_type_ref_id', 'id_type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231010_063354_refactor_shift_type_invalid_intersections cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231010_063354_refactor_shift_type_invalid_intersections cannot be reverted.\n";

        return false;
    }
    */
}
