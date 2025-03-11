<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_194848_alt_add_column_id_code_odopm_to_organizations_table
 */
class m190811_194848_alt_add_column_id_code_odopm_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'id_code_odopm', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'id_code_odopm');
    }

}
