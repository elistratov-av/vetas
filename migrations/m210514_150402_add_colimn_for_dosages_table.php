<?php

use app\commands\migrate\Migration;

/**
 * Class m210514_150402_add_colimn_for_dosages_table
 */
class m210514_150402_add_colimn_for_dosages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tmc.dosages', 'pet_size', $this->string(50)->comment('Развер животного'));
        $this->addColumn('tmc.dosages', 'is_default', $this->boolean()->comment('Дозировка по умолчанию'));

        $this->update('tmc.dosages', [
            'pet_size'   => 'large',
            'is_default' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tmc.dosages', 'pet_size');
        $this->dropColumn('tmc.dosages', 'is_default');
    }
}
