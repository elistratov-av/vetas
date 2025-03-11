<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visit_service_param_values`.
 */
class m180724_014121_create_visit_service_param_values_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_service_param_values', [
            'id' => $this->primaryKey(),
            'num_value' => $this->double(),
            'char_value' => $this->text(),
            'date_value' => $this->integer(),
            'dict_value' => $this->integer(),
            'id_visitservice' => $this->integer()->notNull(),
            'id_param' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_visitservice`
        $this->createIndex(
            'idx-visit_service_param_values-id_visitservice',
            'visit_service_param_values',
            'id_visitservice'
        );

        // add foreign key for table `visits_gov_services`
        $this->addForeignKey(
            'fk-visit_service_param_values-id_visitservice',
            'visit_service_param_values',
            'id_visitservice',
            'visits_gov_services',
            'id',
            'CASCADE'
        );

        // creates index for column `id_param`
        $this->createIndex(
            'idx-visit_service_param_values-id_param',
            'visit_service_param_values',
            'id_param'
        );

        // add foreign key for table `params`
        $this->addForeignKey(
            'fk-visit_service_param_values-id_param',
            'visit_service_param_values',
            'id_param',
            'params',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `visits_gov_services`
        $this->dropForeignKey(
            'fk-visit_service_param_values-id_visitservice',
            'visit_service_param_values'
        );

        // drops index for column `id_visitservice`
        $this->dropIndex(
            'idx-visit_service_param_values-id_visitservice',
            'visit_service_param_values'
        );

        // drops foreign key for table `params`
        $this->dropForeignKey(
            'fk-visit_service_param_values-id_param',
            'visit_service_param_values'
        );

        // drops index for column `id_param`
        $this->dropIndex(
            'idx-visit_service_param_values-id_param',
            'visit_service_param_values'
        );

        $this->dropTable('visit_service_param_values');
    }
}
