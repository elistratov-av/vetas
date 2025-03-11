<?php

use app\commands\migrate\Migration;

/**
 * Class m190417_123953_split_table_vaccine_uses
 */
class m190417_123953_split_table_vaccine_uses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('vaccines_to_species',[
            'id' => $this->primaryKey(),
            'id_vaccine' => $this->integer()->notNull(),
            'id_species' => $this->integer()->notNull(),
        ]);

        $this->addCommentOnTable(
            'vaccines_to_species',
            'Применение вакцины: вид животного'
        );

        $this->createIndex(
            'uniq_vaccines_to_species',
            'vaccines_to_species',
            ['id_vaccine', 'id_species'],
            true
        );

        $this->addForeignKey(
            'fk_vaccines_to_species_id_vaccine',
            'vaccines_to_species',
            'id_vaccine',
            'vaccines',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_vaccines_to_species_id_species',
            'vaccines_to_species',
            'id_species',
            'species',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $sql = "
          INSERT INTO vaccines_to_species (id_vaccine, id_species)
          SELECT DISTINCT id_vaccine, id_species FROM vaccine_uses
          WHERE 
            id_vaccine IS NOT NULL
            AND
            id_disease IS NOT NULL
            AND 
            id_vaccine IN (SELECT id FROM vaccines)
            AND
            id_species IN (SELECT id FROM species)
       ";

        $this->execute($sql);


        $this->createTable('vaccines_to_diseases',[
            'id' => $this->primaryKey(),
            'id_vaccine' => $this->integer()->notNull(),
            'id_disease' => $this->integer()->notNull(),
        ]);

        $this->addCommentOnTable(
            'vaccines_to_diseases',
            'Применение вакцины: заболевание'
        );

        $this->createIndex(
            'uniq_vaccines_to_diseases',
            'vaccines_to_diseases',
            ['id_vaccine', 'id_disease'],
            true
        );

        $this->addForeignKey(
            'fk_vaccines_to_diseases_id_vaccine',
            'vaccines_to_diseases',
            'id_vaccine',
            'vaccines',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_vaccines_to_diseases_id_disease',
            'vaccines_to_diseases',
            'id_disease',
            'diseases',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $sql = "
          INSERT INTO vaccines_to_diseases (id_vaccine, id_disease)
          SELECT DISTINCT id_vaccine, id_disease FROM vaccine_uses
          WHERE 
            id_vaccine IS NOT NULL
            AND
            id_disease IS NOT NULL
            AND 
            id_vaccine IN (SELECT id FROM vaccines)
            AND
            id_disease IN (SELECT id FROM diseases)
       ";

        $this->execute($sql);

        /*
         * Drop table vaccine_uses
         */
        $this->dropForeignKey(
            'fk-vaccine_uses-id_disease',
            'vaccine_uses'
        );

        $this->dropForeignKey(
            'fk-vaccine_uses-id_species',
            'vaccine_uses'
        );

        $this->dropForeignKey(
            'fk-vaccine_uses-id_vaccine',
            'vaccine_uses'
        );

        $this->dropTable('vaccine_uses');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_123953_split_table_vaccine_uses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_123953_split_table_vaccine_uses cannot be reverted.\n";

        return false;
    }
    */
}
