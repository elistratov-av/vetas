<?php

use app\commands\migrate\Migration;

/**
 * Class m210703_084045_add_is_out_org_to_all_vaccinations
 */
class m210703_084045_add_is_out_org_to_all_vaccinations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_dehelmintization', 'is_out_org', $this->boolean()->defaultValue(false)->comment('Проведена организацией вне справочника ВЕТАИС'));
        $this->addColumn('pet_ectoparasites', 'is_out_org', $this->boolean()->defaultValue(false)->comment('Проведена организацией вне справочника ВЕТАИС'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_dehelmintization', 'is_out_org');
        $this->dropColumn('pet_ectoparasites', 'is_out_org');
    }
}
