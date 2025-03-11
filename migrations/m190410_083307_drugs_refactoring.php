<?php

use app\commands\migrate\Migration;

/**
 * Class m190410_083307_drugs_refactoring
 */
class m190410_083307_drugs_refactoring extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->dropColumn('drugs', 'form');
        $this->dropColumn('drugs', 'unit');
        //$this->dropColumn('drugs', 'excipients');
        //$this->dropColumn('drugs', 'basis');

        $this->addColumn(
            'drugs',
            'produced',
            $this->string(255) // Set not null
        );

        $this->addColumn(
            'drugs',
            'registered',
            $this->string(255)  // Set not null
        );

        $this->addColumn(
            'drugs',
            'dealer',
            $this->string(255)
        );

        /*
         * Удаляем те, у которых отсутсвуют связанные записи в справочнике производителей
         */

        $sql = "DELETE FROM drugs WHERE id_produced NOT IN (SELECT id FROM drug_orgs) OR id_produced IS NULL";
        $this->execute($sql);

        $sql = "DELETE FROM drugs WHERE id_registered NOT IN (SELECT id FROM drug_orgs) OR id_registered IS NULL";
        $this->execute($sql);

        /*
         * produced
         */
        $sql = "UPDATE
	drugs
SET
	produced = subquery.name
FROM (
    SELECT drug_orgs.id, drug_orgs.name
     FROM  drug_orgs
    ) AS subquery
WHERE subquery.id = drugs.id_produced;";

        $this->execute($sql);

        /*
         * dealer
         */
        $sql = "UPDATE
	drugs
SET
	dealer = subquery.name
FROM (
    SELECT drug_orgs.id, drug_orgs.name
     FROM  drug_orgs
    ) AS subquery
WHERE subquery.id = drugs.id_dealer;";

        $this->execute($sql);

        /*
         * registered
         */
        $sql = "UPDATE
	drugs
SET
	registered = subquery.name
FROM (
    SELECT drug_orgs.id, drug_orgs.name
     FROM  drug_orgs
    ) AS subquery
WHERE subquery.id = drugs.id_registered;";

        $this->execute($sql);

        $this->execute('ALTER TABLE public.drugs ALTER COLUMN produced SET NOT NULL');
        $this->execute('ALTER TABLE public.drugs ALTER COLUMN registered SET NOT NULL');

        $this->dropColumn(
            'drugs',
            'id_produced'
        );

        $this->dropColumn(
            'drugs',
            'id_dealer'
        );

        $this->dropColumn(
            'drugs',
            'id_registered'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190410_083307_drugs_refactoring cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190410_083307_drugs_refactoring cannot be reverted.\n";

        return false;
    }
    */
}
