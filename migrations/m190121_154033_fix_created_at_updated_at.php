<?php

use app\commands\migrate\Migration;

/**
 * Class m190121_154033_fix_created_at_updated_at
 */
class m190121_154033_fix_created_at_updated_at extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tables = [
            'public.balance_vaccines',
            'public.breeds_diseases',
            'public.fias_addresses',
            'public.gov_services_params',
            'public.gov_services_reports',
            'public.params',
            'public.reg_certificates',
            'public.reports',
            'public.reports_params',
            'public.service_tmcs',
            'public.visit_param_values',
            'public.visit_service_param_values',
            'public.visit_service_tmcs',
        ];

        foreach ($tables as $table) {
            $this->alterColumn($table, 'created_at', $this->timestamp(0));
            $this->alterColumn($table, 'updated_at', $this->timestamp(0));
        }

        $this->addColumn('public.pet_identification', 'created_at', $this->timestamp(0));
        $this->addColumn('public.pet_identification', 'updated_at', $this->timestamp(0));
        $this->addColumn('public.pet_identification', 'created_by', $this->integer());
        $this->addColumn('public.pet_identification', 'updated_by', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190121_154033_fix_created_at_updated_at cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190121_154033_fix_created_at_updated_at cannot be reverted.\n";

        return false;
    }
    */
}
