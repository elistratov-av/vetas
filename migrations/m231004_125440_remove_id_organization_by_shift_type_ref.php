<?php

use app\commands\migrate\Migration;

/**
 * Class m231004_125440_remove_id_organization_by_shift_type_ref
 */
class m231004_125440_remove_id_organization_by_shift_type_ref extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('shift_type_ref', 'id_organization');
        $this->alterColumn('shift_type_ref', 'beginning_of_shift', $this->time());
        $this->alterColumn('shift_type_ref', 'end_of_shift', $this->time());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('shift_type_ref', 'id_organization', $this->integer());
        $this->alterColumn('shift_type_ref', 'beginning_of_shift', $this->timestamp());
        $this->alterColumn('shift_type_ref', 'end_of_shift', $this->timestamp());
    }
}
