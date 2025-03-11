<?php

use yii\db\Migration;

/**
 * Handles the creation of table `drop_columns_in_organization`.
 */
class m210606_163836_create_drop_columns_in_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organizations', 'bti_adm_area_code');
        $this->addColumn('organizations', 'bti_adm_area_code', $this->string()->comment('Код обслуживаемого округа'));
        $this->addColumn('organizations', 'bti_adm_district_code', $this->string()->comment('Код обслуживаемой области'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'bti_adm_area_code');
        $this->dropColumn('organizations', 'bti_adm_district_code');
        $this->addColumn('organizations', 'bti_adm_area_code', $this->integer()->comment('Код обслуживаемого округа'));
    }
}
