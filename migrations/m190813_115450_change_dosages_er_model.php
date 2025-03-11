<?php

use app\commands\migrate\Migration;

/**
 * Class m190813_115450_change_dosages_er_model
 */
class m190813_115450_change_dosages_er_model extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('drop table if exists public.diseases_dosages');
        $this->execute('drop table if exists public.species_dosages');
        $this->execute('drop table if exists public.dosages');

        $sql = <<<SQL
      CREATE TABLE public.dosages(
      	id serial not null
		constraint dosages_pkey
			primary key,
        dosage integer not null,
        id_drug integer not null
            constraint "fk-dosages-id_drug"
                references drugs,
        id_measure integer not null
            constraint "fk-dosages-id_measure"
                references measures,
        id_species integer not null
            constraint "fk-dosages-id_species"
                references species,
        id_disease integer not null
            constraint "fk-dosages-id_disease"
                references diseases,
        created_by integer Null, 
        updated_by integer Null, 
        created_at timestamp Null, 
        updated_at timestamp Null,
      
        age_range int4range,
        weight_range int4range not null,
        CONSTRAINT age_range_constraint EXCLUDE USING GIST (id_drug WITH =, id_species WITH =, id_disease WITH =, age_range WITH  &&),
        CONSTRAINT weight_range_constraint EXCLUDE USING GIST (id_drug WITH =, id_species WITH =, id_disease WITH =,weight_range WITH &&)
      ); 
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190813_115450_change_dosages_er_model cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190813_115450_change_dosages_er_model cannot be reverted.\n";

        return false;
    }
    */
}
