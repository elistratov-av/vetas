<?php

use app\commands\migrate\Migration;

/**
 * Class m231004_132553_remove_id_organization_by_shift_type_invalid_intersections
 */
class m231004_132553_remove_id_organization_by_shift_type_invalid_intersections extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('shift_type_invalid_intersections', 'id_organization');
        $this->dropColumn('shift_type_invalid_intersections', 'id_type_first');
        $this->dropColumn('shift_type_invalid_intersections', 'id_type_second');
        $this->addColumn('shift_type_invalid_intersections', 'id_type', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('shift_type_invalid_intersections', 'id_organization', $this->integer());
        $this->addColumn('shift_type_invalid_intersections', 'id_type_first', $this->integer());
        $this->addColumn('shift_type_invalid_intersections', 'id_type_second', $this->integer());
        $this->dropColumn('shift_type_invalid_intersections', 'id_type');
    }
}
