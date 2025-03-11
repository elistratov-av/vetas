<?php

use app\commands\migrate\Migration;

/**
 * Class m210617_083006_fix_constraints_for_violation_ARV
 */
class m210617_083006_fix_constraints_for_violation_ARV extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-violation-arv-id_violation', 'violation_ARV');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {}
}
