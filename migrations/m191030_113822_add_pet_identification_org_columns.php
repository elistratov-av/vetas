<?php

use app\commands\migrate\Migration;

/**
 * Class m191030_113822_add_pet_identification_org_columns
 */
class m191030_113822_add_pet_identification_org_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.pet_identification', 'identif_comp', $this->integer()->defaultValue(null));
        $this->addForeignKey('comp-pet-ident-fk', 'public.pet_identification', 'identif_comp', 'public.companies', 'id');
        $this->addColumn('public.pet_identification', 'identif_org', $this->integer()->defaultValue(null));
        $this->addForeignKey('org-pet-ident-fk', 'public.pet_identification', 'identif_org', 'public.organizations', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('comp-pet-ident-fk', 'public.pet_identification');
        $this->dropColumn('public.pet_identification', 'identif_comp');
        $this->dropForeignKey('org-pet-ident-fk', 'public.pet_identification');
        $this->dropColumn('public.pet_identification', 'identif_org');

//        echo "m191030_113822_add_pet_identification_org_columns cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191030_113822_add_pet_identification_org_columns cannot be reverted.\n";

        return false;
    }
    */
}
