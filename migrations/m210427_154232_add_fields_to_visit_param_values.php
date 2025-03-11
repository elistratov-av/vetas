<?php

use app\commands\migrate\Migration;

/**
 * Class m210427_154232_add_fields_to_visit_param_values
 */
class m210427_154232_add_fields_to_visit_param_values extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //visit_service_tmc_pet
        $this->createTable(
            'public.visit_service_tmc_pet', [
                'id'                    => $this->primaryKey(),
                'id_visit'              => $this->integer()->notNull()->comment('id приема'),
                'id_visits_gov_service' => $this->integer()->notNull()->comment('id услуги'),
                'id_visit_service_tmc'  => $this->integer()->notNull()->comment('id ТМЦ услуги'),
                'id_pet'                => $this->integer()->notNull()->comment('id животного'),
                'type_tmc'              => 'tmc.tmc_class_list',
            ]
        );

        $this->addCommentOnColumn('public.visit_service_tmc_pet', 'type_tmc', 'Тип ТМЦ');

        $this->createIndex('idx-visit_service_tmc_pet-id_visit', 'public.visit_service_tmc_pet', 'id_visit');
        $this->createIndex('idx-visit_service_tmc_pet-id_visits_gov_service', 'public.visit_service_tmc_pet', 'id_visits_gov_service');
        $this->createIndex('idx-visit_service_tmc_pet-id_visit_service_tmc', 'public.visit_service_tmc_pet', 'id_visit_service_tmc');

        $this->addForeignKey('fk-visit_service_tmc_pet-id_visit', 'public.visit_service_tmc_pet', 'id_visit', 'public.visits', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-visit_service_tmc_pet-id_visits_gov_service', 'public.visit_service_tmc_pet', 'id_visits_gov_service', 'public.visits_gov_services', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-visit_service_tmc_pet-id_visit_service_tmc', 'public.visit_service_tmc_pet', 'id_visit_service_tmc', 'public.visit_service_tmc', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-visit_service_tmc_pet-id_pet', 'public.visit_service_tmc_pet', 'id_pet', 'public.pets', 'id', 'CASCADE', 'CASCADE');

        //visit_param_values
        $this->addColumn('public.visit_param_values', 'id_visitservice', $this->integer()->comment('id услуги'));
        $this->addColumn('public.visit_param_values', 'id_visit_service_tmc', $this->integer()->comment('id ТМЦ услуги'));
        $this->addColumn('public.visit_param_values', 'id_pet', $this->integer()->comment('id животного'));
        $this->addColumn('public.visit_param_values', 'migrate_flag', $this->smallInteger()->comment('Временный флаг миграции отчетов'));
        $this->createIndex('idx-visit_param_values-id_visitservice', 'public.visit_param_values', 'id_visitservice');
        $this->createIndex('idx-visit_param_values-id_visit_service_tmc', 'public.visit_service_tmc_pet', 'id_visit_service_tmc');
        $this->createIndex('idx-visit_param_values-id_pet', 'public.visit_param_values', 'id_pet');
        $this->addForeignKey(
            'fk-visit_param_values-id_visitservice',
            'public.visit_param_values',
            'id_visitservice',
            'public.visits_gov_services',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-visit_param_values-id_visit_service_tmc',
            'public.visit_param_values',
            'id_visit_service_tmc',
            'public.visit_service_tmc',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-visit_param_values-id_pet',
            'public.visit_param_values',
            'id_pet',
            'public.pets',
            'id',
            'SET NULL',
            'CASCADE'
        );

        //visit_service_param_values
        $this->addColumn('public.visit_service_param_values', 'id_visit', $this->integer()->comment('id приема'));
        $this->addColumn('public.visit_service_param_values', 'id_visit_service_tmc', $this->integer()->comment('id ТМЦ услуги'));
        $this->addColumn('public.visit_service_param_values', 'id_pet', $this->integer()->comment('id животного'));
        $this->addColumn('public.visit_service_param_values', 'migrate_flag', $this->smallInteger()->comment('Временный флаг миграции отчетов'));
        $this->createIndex('idx-visit_service_param_values-id_visit', 'public.visit_service_param_values', 'id_visit');
        $this->createIndex('idx-visit_service_param_values-id_visit_service_tmc', 'public.visit_service_param_values', 'id_visit_service_tmc');
        $this->createIndex('idx-visit_service_param_values-id_pet', 'public.visit_service_param_values', 'id_pet');
        $this->addForeignKey(
            'fk-visit_service_param_values-id_visit',
            'public.visit_service_param_values',
            'id_visit',
            'public.visits',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-visit_service_param_values-id_visit_service_tmc',
            'public.visit_service_param_values',
            'id_visit_service_tmc',
            'public.visit_service_tmc',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-visit_service_param_values-id_pet',
            'public.visit_service_param_values',
            'id_pet',
            'public.pets',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-visit_service_tmc_pet-id_visit', 'public.visit_service_tmc_pet');
        $this->dropForeignKey('fk-visit_service_tmc_pet-id_visits_gov_services', 'public.visit_service_tmc_pet');
        $this->dropForeignKey('fk-visit_service_tmc_pet-id_visit_service_tmc', 'public.visit_service_tmc_pet');
        $this->dropForeignKey('fk-visit_service_tmc_pet-id_pet', 'public.visit_service_tmc_pet');
        $this->dropTable('public.visit_service_tmc_pet');

        $this->dropForeignKey('fk-visit_param_values-id_visitservice', 'public.visit_param_values');
        $this->dropForeignKey('fk-visit_param_values-id_visit_service_tmc', 'public.visit_param_values');
        $this->dropForeignKey('fk-visit_param_values-id_pet', 'public.visit_param_values');

        $this->dropForeignKey('fk-visit_service_param_values-id_visit', 'public.visit_service_param_values');
        $this->dropForeignKey('fk-visit_service_param_values-id_visit_service_tmc', 'public.visit_service_param_values');
        $this->dropForeignKey('fk-visit_service_param_values-id_pet', 'public.visit_service_param_values');

        $this->dropColumn('public.visit_param_values', 'id_visitservice');
        $this->dropColumn('public.visit_param_values', 'id_visit_service_tmc');
        $this->dropColumn('public.visit_param_values', 'id_pet');

        $this->dropColumn('public.visit_service_param_values', 'id_visit');
        $this->dropColumn('public.visit_service_param_values', 'id_visit_service_tmc');
        $this->dropColumn('public.visit_service_param_values', 'id_pet');

        $this->dropColumn('public.visit_param_values', 'migrate_flag');
        $this->dropColumn('public.visit_service_param_values', 'migrate_flag');
    }
}
