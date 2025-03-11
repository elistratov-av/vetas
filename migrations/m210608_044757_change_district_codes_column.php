<?php

use app\commands\migrate\Migration;

/**
 * Class m210608_044757_change_district_codes_column
 */
class m210608_044757_change_district_codes_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organizations', 'bti_adm_district_codes');
        $this->execute("ALTER TABLE organizations ADD COLUMN bti_adm_district_codes text[];");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'bti_adm_district_codes');
        $this->addColumn('organizations', 'bti_adm_district_code', $this->string()->comment('Код улицы'));
    }
}
