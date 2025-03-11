<?php

use app\commands\migrate\Migration;


/**
 * Handles the creation of table `dosages`.
 */
class m190808_043917_create_dosages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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
        weight_range int4range,
        EXCLUDE USING GIST (age_range WITH &&),
        EXCLUDE USING GIST (weight_range WITH &&)
      ); 
SQL;

        $this->execute($sql);

        $this->execute('CREATE INDEX "idx-dosages-age" ON dosages USING GIST (age_range)');
        $this->execute('CREATE INDEX "idx-dosages-weight" ON dosages USING GIST (weight_range)');

        $this->addCommentOnTable('dosages', 'Настройки дозировки препарата');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-dosages-id_drug',
            'dosages');

        $this->dropForeignKey('fk-dosages-id_measure',
            'dosages');

        $this->dropTable('dosages');
    }
}
