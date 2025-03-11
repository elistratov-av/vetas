<?php

use yii\db\Migration;

/**
 * Class m180724_012808_refactor_visit_param_values_table
 */
class m180724_012808_refactor_visit_param_values_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('visit_param_values', 'char_value', $this->text());
        $this->dropForeignKey('fk-visit_param_values-id_visitservice', 'visit_param_values');
        $this->dropIndex('idx-visit_param_values-id_visitservice', 'visit_param_values');
        $this->renameColumn('visit_param_values', 'id_visitservice', 'id_visit');
        $this->createIndex(
            'idx-visit_param_values-id_visit',
            'visit_param_values',
            'id_visit'
        );
        $this->addForeignKey(
            'fk-visit_param_values-id_visit',
            'visit_param_values',
            'id_visit',
            'visits',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180724_012808_refactor_visit_param_values_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_012808_refactor_visit_param_values_table cannot be reverted.\n";

        return false;
    }
    */
}
