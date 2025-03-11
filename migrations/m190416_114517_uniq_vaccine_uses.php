<?php

use app\commands\migrate\Migration;

/**
 * Class m190416_114517_uniq_vaccine_uses
 */
class m190416_114517_uniq_vaccine_uses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql ="
DELETE FROM
    vaccine_uses a
        USING vaccine_uses b
WHERE
        a.id > b.id
    AND 
        a.id_vaccine = b.id_vaccine
    AND
        a.id_species = b.id_species
    AND
        a.id_disease = b.id_disease
;";

        $this->execute($sql);

        $this->createIndex(
            'uniq_vaccine_uses',
            'vaccine_uses',
            ['id_vaccine', 'id_species', 'id_disease'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'uniq_vaccine_uses',
            'vaccine_uses'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190416_114517_uniq_vaccine_uses cannot be reverted.\n";

        return false;
    }
    */
}
