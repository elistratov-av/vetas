<?php

use app\commands\migrate\Migration;

/**
 * Class m191204_041530_1964_odopm_fix_organizations_2
 */
class m191204_041530_1964_odopm_fix_organizations_2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organizations', 'id_code_odopm');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('organizations', 'id_code_odopm', $this->integer());
    }
}
