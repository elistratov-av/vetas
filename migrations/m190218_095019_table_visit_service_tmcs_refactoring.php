<?php

use app\commands\migrate\Migration;

/**
 * Class m190218_095019_table_visit_service_tmcs_refactoring
 */
class m190218_095019_table_visit_service_tmcs_refactoring extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Удаляем старую

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

        // Создаем новую

        $this->execute("CREATE TYPE visit_service_tmc_type_list AS ENUM (
            'drugs', 'equipments', 'exp_materials', 'vaccines',
            'balance_equipments', 'balance_vaccines', 'balance_exp_materials', 'balance_drugs'
        );");

        $this->createTable('visit_service_tmc', [
            'id' => $this->primaryKey(),
            'id_visit' => $this->integer()->notNull(),
            'id_visits_gov_service' => $this->integer()->notNull(),

            'id_entity' => $this->integer()->notNull(),
            'id_measure' => $this->integer(),
            'id_organization' => $this->integer(),
            'entity_type' => 'visit_service_tmc_type_list NOT NULL',
            'name' => $this->string(),
            'inventory_number' => $this->string(255),
            'measure_name' => $this->string(50),
            'count' => $this->integer(),
            'price' => $this->decimal(8, 2),
            'apply_discount' => $this->boolean()->notNull()->defaultValue('false'),
            'apply_night_discount' => $this->boolean()->notNull()->defaultValue('false'),
        ]);

        /*
         * id_visit
        */
        $this->addForeignKey(
            'fk-visit_service_tmc-id_visits',
            'visit_service_tmc',
            'id_visit',
            'visits',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*
         * id_visits_gov_service
         */
        $this->addForeignKey(
            'fk-visit_service_tmc-id_visits_gov_service',
            'visit_service_tmc',
            'id_visits_gov_service',
            'visits_gov_services',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190218_095019_table_visit_service_tmcs_refactoring cannot be reverted.\n";
        return false;
        /*$this->dropForeignKey('fk-visit_service_tmc-id_visits', 'visit_service_tmc');
        $this->dropForeignKey('fk-visit_service_tmc-id_visits_gov_service', 'visit_service_tmc');

        $this->dropTable('visit_service_tmc');

        $this->execute("DROP TYPE IF EXISTS visit_service_tmc_type_list");*/
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190218_095019_table_visit_service_tmcs_refactoring cannot be reverted.\n";

        return false;
    }
    */
}
