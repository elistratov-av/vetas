<?php

use app\commands\migrate\Migration;

/**
 * Class m210525_000547_add_column_to_category
 */
class m210525_000547_add_column_to_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tmc.category', 'slug', $this->string(50)->comment('Слаг'));
        $this->insert('tmc.category', [
            'name'        => 'Бланки вет. свидетельства',
            'description' => 'Бланки ветеринарного свидетельства',
            'slug'        => 'veterinary_certificate_forms',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('tmc.category', ['slug' => 'veterinary_certificate_forms']);
        $this->dropColumn('tmc.category', 'slug');
    }
}
