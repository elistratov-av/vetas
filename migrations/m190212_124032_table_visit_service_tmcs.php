<?php

use app\commands\migrate\Migration;

/**
 * Class m190212_124032_table_visit_service_tmcs
 */
class m190212_124032_table_visit_service_tmcs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drop old table
        $this->dropForeignKey('fk-visit_service_tmcs-id_service', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_visit', 'visit_service_tmcs');
        $this->dropTable('visit_service_tmcs');

        // create new
        $this->createTable('visit_service_tmcs', [
            'id' => $this->primaryKey(),
            'id_visits_gov_service' => $this->integer()->notNull(),

            'id_balance_drugs' => $this->integer(),
            'id_balance_equipments' => $this->integer(),
            'id_balance_exp_material' => $this->integer(),
            'id_balance_vaccines' => $this->integer(),

            'id_drugs' => $this->integer(),
            'id_equipment' => $this->integer(),
            'id_exp_material' => $this->integer(),
            'id_vaccine' => $this->integer(),

            'count' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        /*
         * id_visit
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_visits_gov_service',
            'visit_service_tmcs',
            'id_visits_gov_service',
            'visits_gov_services',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_balance_drugs
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_balance_drugs',
            'visit_service_tmcs',
            'id_balance_drugs',
            'balance_drugs',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_balance_equipments
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_balance_equipments',
            'visit_service_tmcs',
            'id_balance_equipments',
            'balance_equipments',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_balance_vaccines
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_balance_vaccines',
            'visit_service_tmcs',
            'id_balance_vaccines',
            'balance_vaccines',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_balance_exp_material
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_balance_exp_material',
            'visit_service_tmcs',
            'id_balance_exp_material',
            'balance_exp_materials',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_drugs
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_drugs',
            'visit_service_tmcs',
            'id_drugs',
            'drugs',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_equipment
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_equipment',
            'visit_service_tmcs',
            'id_equipment',
            'equipments',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_exp_material
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_exp_material',
            'visit_service_tmcs',
            'id_exp_material',
            'exp_materials',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_vaccine
         */
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_vaccine',
            'visit_service_tmcs',
            'id_vaccine',
            'vaccines',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addCommentOnTable('visit_service_tmcs', 'Список использованых ТМЦ в приеме');

        $sql = <<<SQL
CREATE FUNCTION check_fk_in_visit_service_tmcs_func()
RETURNS trigger AS \$BODY\$

DECLARE
    count_fk integer;
BEGIN
count_fk := 0;

IF NEW.id_balance_drugs IS NOT NULL THEN count_fk := count_fk +1; END IF;
IF NEW.id_balance_equipments IS NOT NULL THEN count_fk := count_fk +1; END IF;
IF NEW.id_balance_exp_material IS NOT NULL THEN count_fk := count_fk +1; END IF;
IF NEW.id_balance_vaccines IS NOT NULL THEN count_fk := count_fk +1; END IF;
IF NEW.id_drugs IS NOT NULL THEN count_fk := count_fk +1; END IF;
IF NEW.id_equipment IS NOT NULL THEN count_fk := count_fk +1; END IF;
IF NEW.id_exp_material IS NOT NULL THEN count_fk := count_fk +1; END IF;
IF NEW.id_vaccine IS NOT NULL THEN count_fk := count_fk +1; END IF;

IF count_fk > 1 THEN
    RAISE EXCEPTION 'Only one FK balance/tmc can be NOT NULL';
END IF;

IF count_fk < 1 THEN
    RAISE EXCEPTION 'Set one FK balance/tmc';
END IF;

	RETURN NEW;
END;

\$BODY\$
LANGUAGE 'plpgsql';
SQL;

        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER check_fk_in_visit_service_tmcs_trg
BEFORE INSERT OR UPDATE
ON visit_service_tmcs
FOR EACH ROW
EXECUTE PROCEDURE check_fk_in_visit_service_tmcs_func();
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER 
            check_fk_in_visit_service_tmcs_trg
            ON visit_service_tmcs
        ');
        $this->execute('DROP FUNCTION
            check_fk_in_visit_service_tmcs_func
        ');

        $this->dropForeignKey('fk-visit_service_tmcs-id_visits_gov_service', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_balance_drugs', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_balance_equipments', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_balance_vaccines', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_balance_exp_material', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_drugs', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_equipment', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_exp_material', 'visit_service_tmcs');
        $this->dropForeignKey('fk-visit_service_tmcs-id_vaccine', 'visit_service_tmcs');

        $this->dropTable('visit_service_tmcs');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190212_124032_table_visit_service_tmcs cannot be reverted.\n";

        return false;
    }
    */
}
