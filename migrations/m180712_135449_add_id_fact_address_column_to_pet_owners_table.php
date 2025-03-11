<?php

use yii\db\Migration;

/**
 * Handles adding id_fact_address to table `pet_owners`.
 */
class m180712_135449_add_id_fact_address_column_to_pet_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'id_fact_address', $this->integer());
        $this->addForeignKey(
            'fk-pet_owners-id_fact_address',
            'pet_owners',
            'id_fact_address',
            'addresses',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pet_owners-id_fact_address', 'pet_owners');
        $this->dropColumn('pet_owners', 'id_fact_address');
    }
}
