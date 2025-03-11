<?php

use app\commands\migrate\Migration;

/**
 * Class m231011_070653_add_column_shift_type_ref_id_by_shifts
 */
class m231011_070653_add_column_shift_type_ref_id_by_shifts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('shifts', 'shift_type_ref_id', $this->integer());
        $this->dropIndex('uniq-shifts_name_id_organization', 'shifts');
        $this->createIndex('uniq-shifts_name_id_organization_shift_type_ref_id', 'shifts', ['name', 'id_organization', 'shift_type_ref_id'], TRUE);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('shifts', 'shift_type_ref_id');
        $this->dropIndex('uniq-shifts_name_id_organization_shift_type_ref_id', 'shifts');
        $this->createIndex('uniq-shifts_name_id_organization', 'shifts', ['name', 'id_organization'], TRUE);
    }
}
