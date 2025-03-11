<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visit_param_values`.
 * Has foreign keys to the tables:
 *
 * - `visits_gov_services`
 * - `params`
 */
class m180717_200836_create_visit_param_values_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_param_values', [
            'id' => $this->primaryKey(),
            'num_value' => $this->double(),
            'char_value' => $this->string(),
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
            'idx-visit_param_values-id_visitservice',
            'visit_param_values',
            'id_visitservice'
        );

        // add foreign key for table `visits_gov_services`
        $this->addForeignKey(
            'fk-visit_param_values-id_visitservice',
            'visit_param_values',
            'id_visitservice',
            'visits_gov_services',
            'id',
            'CASCADE'
        );

        // creates index for column `id_param`
        $this->createIndex(
            'idx-visit_param_values-id_param',
            'visit_param_values',
            'id_param'
        );

        // add foreign key for table `params`
        $this->addForeignKey(
            'fk-visit_param_values-id_param',
            'visit_param_values',
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
            'fk-visit_param_values-id_visitservice',
            'visit_param_values'
        );

        // drops index for column `id_visitservice`
        $this->dropIndex(
            'idx-visit_param_values-id_visitservice',
            'visit_param_values'
        );

        // drops foreign key for table `params`
        $this->dropForeignKey(
            'fk-visit_param_values-id_param',
            'visit_param_values'
        );

        // drops index for column `id_param`
        $this->dropIndex(
            'idx-visit_param_values-id_param',
            'visit_param_values'
        );

        $this->dropTable('visit_param_values');
    }
}
