<?php

use app\commands\migrate\Migration;

/**
 * Class m231006_135932_refactor_shift_type_invalid_intersections
 */
class m231006_135932_refactor_shift_type_invalid_intersections extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('shift_type_invalid_intersections', 'id_type', 'shift_type_ref_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231006_135932_refactor_shift_type_invalid_intersections cannot be reverted.\n";

        return false;
    }
}
