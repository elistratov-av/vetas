<?php

use yii\db\Migration;

/**
 * Class m180628_114027_refactor_drug_orgs
 */
class m180628_114027_refactor_drug_orgs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-drugs-id_manufactured', 'drugs');
        $this->dropForeignKey('fk-drugs-id_registered', 'drugs');
        $this->dropForeignKey('fk-drugs-id_representation', 'drugs');

        $this->addForeignKey('fk-drugs-id_manufactured', 'drugs',
            'id_manufactured',
            'drug_orgs',
            'id',
            'CASCADE',
            'CASCADE');

        $this->addForeignKey('fk-drugs-id_registered', 'drugs', 'id_registered',
            'drug_orgs',
            'id',
            'CASCADE',
            'CASCADE');

        $this->addForeignKey('fk-drugs-id_representation', 'drugs',
            'id_representation',
            'drug_orgs',
            'id',
            'CASCADE',
            'CASCADE');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180628_114027_refactor_drug_orgs cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180628_114027_refactor_drug_orgs cannot be reverted.\n";

        return false;
    }
    */
}
