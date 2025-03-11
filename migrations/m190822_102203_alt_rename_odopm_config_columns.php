<?php

use app\commands\migrate\Migration;

/**
 * Class m190822_102203_alt_rename_odopm_config_columns
 */
class m190822_102203_alt_rename_odopm_config_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('odopm.odopm_config', 'free_vacination', 'free_vaccination');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('odopm.odopm_config', 'free_vaccination', 'free_vacination');
    }

}
