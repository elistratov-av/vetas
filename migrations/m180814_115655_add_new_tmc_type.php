<?php

use yii\db\Migration;

/**
 * Class m180814_115655_add_new_tmc_type
 */
class m180814_115655_add_new_tmc_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->execute("ALTER TYPE public.tmc_class_list ADD VALUE IF NOT EXISTS 'exp_material' AFTER 'equipment'");
        $this->insert('tmc_types', [
            'name' => 'Расходные материалы',
            'description' => 'Расходные материалы',
            'tmc_class' => 'exp_material'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->delete('tmc_types', [
                'name' => 'Расходные материалы',
                'description' => 'Расходные материалы',
                'tmc_class' => 'exp_material'
            ]);
    }

}
