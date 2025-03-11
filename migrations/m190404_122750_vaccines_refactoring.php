<?php

use app\commands\migrate\Migration;

/**
 * Class m190404_122750_vaccines_refactoring
 */
class m190404_122750_vaccines_refactoring extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE "tmc" DROP COLUMN "id_tmc_type" CASCADE');

        $this->dropColumn('tmc', 'id_tmc_type');
        $this->dropColumn('vaccines', 'form');
        $this->dropColumn('vaccines', 'unit');
        $this->dropColumn('vaccines', 'excipients');
        $this->dropColumn('vaccines', 'basis');

        $this->addColumn(
            'vaccines',
            'produced',
            $this->string(255) // Set not null
        );

        $this->addColumn(
            'vaccines',
            'registered',
            $this->string(255)  // Set not null
        );

        $this->addColumn(
            'vaccines',
            'dealer',
            $this->string(255)
        );

        /*
         * Удаляем те, у которых отсутсвуют связанные записи в справочнике производителей
         */

        $sql = "DELETE FROM vaccines WHERE id_produced NOT IN (SELECT id FROM drug_orgs) OR id_produced IS NULL;";
        $this->execute($sql);

        $sql = "DELETE FROM vaccines WHERE id_registered NOT IN (SELECT id FROM drug_orgs)  OR id_registered IS NULL";
        $this->execute($sql);

        /*
         * produced
         */
        $sql = "UPDATE
	vaccines
SET
	produced = subquery.name
FROM (
    SELECT drug_orgs.id, drug_orgs.name
     FROM  drug_orgs
    ) AS subquery
WHERE subquery.id = vaccines.id_produced;";

        $this->execute($sql);

        /*
         * dealer
         */
        $sql = "UPDATE
	vaccines
SET
	dealer = subquery.name
FROM (
    SELECT drug_orgs.id, drug_orgs.name
     FROM  drug_orgs
    ) AS subquery
WHERE subquery.id = vaccines.id_dealer;";

        $this->execute($sql);

        /*
         * registered
         */
        $sql = "UPDATE
	vaccines
SET
	registered = subquery.name
FROM (
    SELECT drug_orgs.id, drug_orgs.name
     FROM  drug_orgs
    ) AS subquery
WHERE subquery.id = vaccines.id_registered;";

        $this->execute($sql);


        $this->execute('ALTER TABLE public.vaccines ALTER COLUMN produced SET NOT NULL');
        $this->execute('ALTER TABLE public.vaccines ALTER COLUMN registered SET NOT NULL');

        $this->dropColumn(
            'vaccines',
            'id_produced'
        );

        $this->dropColumn(
            'vaccines',
            'id_dealer'
        );

        $this->dropColumn(
            'vaccines',
            'id_registered'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190404_122750_vaccines_refactoring cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190404_122750_vaccines_refactoring cannot be reverted.\n";

        return false;
    }
    */
}
