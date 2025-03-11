<?php

use app\commands\migrate\Migration;

/**
 * Class m190419_085017_params_1740_add_flag_in_flag_out
 */
class m190419_085017_params_1740_add_flag_in_flag_out extends Migration
{
    private $tableName = 'gov_services_params';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (['flag_in', 'flag_out'] as $column) {
            $this->addColumn('{{%' . $this->tableName . '}}', $column, $this->boolean()->defaultValue(false));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['flag_in', 'flag_out'] as $column) {
            $this->dropColumn('{{%' . $this->tableName . '}}', $column);
        }
    }
}
