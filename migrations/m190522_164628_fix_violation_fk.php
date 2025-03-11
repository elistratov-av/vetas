<?php

use app\commands\migrate\Migration;

/**
 * Class m190522_164628_fix_violation_fk
 */
class m190522_164628_fix_violation_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-violation-id_disease',
            'violation'
        );

        $this->addForeignKey(
            'fk-violation-id_disease',
            'violation',
            'id_disease',
            'diseases',
            'id',
            'NO ACTION',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190522_164628_fix_violation_fk cannot be reverted.\n";

        return false;
    }
    */
}
