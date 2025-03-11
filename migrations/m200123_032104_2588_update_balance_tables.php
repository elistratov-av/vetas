<?php

use app\commands\migrate\Migration;

/**
 * Class m200123_032104_2588_update_balance_tables
 */
class m200123_032104_2588_update_balance_tables extends Migration
{
    private $columns = [
        'balance_drugs' => 'dose_count',
        'balance_exp_materials' => 'count',
        'balance_vaccines' => 'dose_count',
        'balance_flow' => 'count',
        'visit_service_tmc' => 'count',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->columns as $table => $column) {
            $this->alterColumn($table, $column, $this->decimal(11, 2));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->columns as $table => $column) {
            $this->alterColumn($table, $column, $this->integer());
        }
    }
}
