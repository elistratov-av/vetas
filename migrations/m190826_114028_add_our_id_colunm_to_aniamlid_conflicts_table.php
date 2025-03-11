<?php

use app\commands\migrate\Migration;

/**
 * Class m190826_114028_add_our_id_colunm_to_aniamlid_conflicts_table
 */
class m190826_114028_add_our_id_colunm_to_aniamlid_conflicts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('animalid.conflicts', 'our_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('animalid.conflicts', 'our_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190826_114028_add_our_id_colunm_to_aniamlid_conflicts_table cannot be reverted.\n";

        return false;
    }
    */
}
