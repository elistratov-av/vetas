<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_090120_change_dosages_ranges_check
 */
class m190811_090120_change_dosages_ranges_check extends Migration
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
        created_by integer Null, 
        updated_by integer Null, 
        created_at timestamp Null, 
        updated_at timestamp Null,
      
        age_range int4range,
        weight_range int4range not null,
        CONSTRAINT age_range_constraint EXCLUDE USING GIST (id_drug WITH =, age_range WITH &&),
        CONSTRAINT weight_range_constraint EXCLUDE USING GIST (id_drug WITH =, weight_range WITH &&)
      ); 
SQL;

        $this->execute($sql);

        $this->addCommentOnTable('dosages', 'Настройки дозировки препарата');

        $this->createTable('diseases_dosages', [
            'id' => $this->primaryKey(),
            'id_dosage' => $this->integer()->notNull(),
            'id_disease' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        $this->addForeignKey('fk-diseases_dosages-id_dosage',
            'diseases_dosages',
            'id_dosage',
            'dosages',
            'id',
            'CASCADE'
        );

        $this->addForeignKey('fk-diseases_dosages-id_disease',
            'diseases_dosages',
            'id_disease',
            'diseases',
            'id',
            'CASCADE'
        );

        $this->createTable('species_dosages', [
            'id' => $this->primaryKey(),
            'id_dosage' => $this->integer()->notNull(),
            'id_species' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);


        $this->addForeignKey('fk-species_dosages-id_dosage',
            'species_dosages',
            'id_dosage',
            'dosages',
            'id',
            'CASCADE'
        );


        $this->addForeignKey('fk-species_dosages-id_breed',
            'species_dosages',
            'id_species',
            'species',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190811_090120_change_dosages_ranges_check cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190811_090120_change_dosages_ranges_check cannot be reverted.\n";

        return false;
    }
    */
}
