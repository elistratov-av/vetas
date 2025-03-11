<?php

use app\commands\migrate\Migration;

/**
 * Class m190116_084145_add_id_fact_fias_address_to_pet_owners_table
 */
class m190116_084145_add_id_fact_fias_address_to_pet_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'id_fact_fias_address', $this->integer());
        $this->addCommentOnColumn('pet_owners', 'id_fact_fias_address', 'Ссылка на адрес ФИАС, таблица fias_address');

        $this->addColumn('pet_owners', 'id_fact_address', $this->integer());
        $this->addForeignKey(
            'fk-pet_owners-id_fact_fias_address',
            'pet_owners',
            'id_fact_fias_address',
            'fias_addresses',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pet_owners-id_fact_fias_address', 'pet_owners');
        $this->dropColumn('pet_owners', 'id_fact_fias_address');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190116_084145_add_id_fact_fias_address_to_pet_owners_table cannot be reverted.\n";

        return false;
    }
    */
}
