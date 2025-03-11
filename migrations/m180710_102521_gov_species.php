<?php

use yii\db\Migration;

/**
 * Class m180710_102521_gov_species
 */
class m180710_102521_gov_species extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('gov_services', 'id_species', 'integer');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180710_102521_gov_species cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180710_102521_gov_species cannot be reverted.\n";

        return false;
    }
    */
}
