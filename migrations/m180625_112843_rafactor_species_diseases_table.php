<?php

use yii\db\Migration;

/**
 * Class m180625_112843_rafactor_species_diseases_table
 */
class m180625_112843_rafactor_species_diseases_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE species_diseases DROP COLUMN IF EXISTS "name"');
        $this->execute('ALTER TABLE species_diseases DROP COLUMN IF EXISTS "description"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180625_112843_rafactor_species_diseases_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180625_112843_rafactor_species_diseases_table cannot be reverted.\n";

        return false;
    }
    */
}
