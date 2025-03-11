<?php

use app\commands\migrate\Migration;

/**
 * Class m210831_164206_add_field_to_fias_address_table
 */
class m210831_164206_add_field_to_fias_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('fias_addresses', 'description', $this->text()->comment('Примечание адреса регистрации'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('fias_addresses', 'description');
    }
}
