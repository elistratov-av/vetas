<?php

use yii\db\Migration;

/**
 * Handles adding bti_adm_area_code to table `organizations`.
 */
class m210525_061242_add_bti_adm_area_code_column_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'bti_adm_area_code', $this->integer()->comment('Код обслуживаемого округа'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'bti_adm_area_code');
    }
}
