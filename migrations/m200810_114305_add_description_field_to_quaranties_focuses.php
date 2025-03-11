<?php

use app\commands\migrate\Migration;

/**
 * Class m200810_114305_add_description_field_to_quaranties_focuses
 */
class m200810_114305_add_description_field_to_quaranties_focuses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('quarantines_focuses', 'description', $this->string());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('quarantines_focuses', 'description');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200810_114305_add_description_field_to_quaranties_focuses cannot be reverted.\n";

        return false;
    }
    */
}
