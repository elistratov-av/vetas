<?php

use yii\db\Migration;

/**
 * Class m180711_032614_fix_species_diseases
 */
class m180711_032614_fix_species_diseases extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE species_diseases DROP COLUMN IF EXISTS name');
        $this->execute('ALTER TABLE species_diseases DROP COLUMN IF EXISTS description');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180711_032614_fix_species_diseases cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180711_032614_fix_species_diseases cannot be reverted.\n";

        return false;
    }
    */
}
