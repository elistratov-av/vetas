<?php

use app\commands\migrate\Migration;

/**
 * Class m190218_091227_add_column_tech_name_to_species_table
 */
class m190218_091227_add_column_tech_name_to_species_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('species', 'tech_name', $this->string(50));
        $sql = "
        UPDATE public.species SET tech_name = 'CAT'
        WHERE species.id = 9";
        $this->execute($sql);
        
        $sql = "
        UPDATE public.species SET tech_name = 'HORSE'
        WHERE species.id = 14";
        $this->execute($sql);
        
        $sql = "
        UPDATE public.species SET tech_name = 'DOG'
        WHERE species.id = 25";
        $this->execute($sql);

        $this->createIndex(
            'idx-species-tech_name',
            'species',
            'tech_name',
            true
        );


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('species', 'tech_name');
//        echo "m190218_091227_add_column_tech_name_to_species_table cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190218_091227_add_column_tech_name_to_species_table cannot be reverted.\n";

        return false;
    }
    */
}
