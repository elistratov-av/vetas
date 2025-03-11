<?php

use app\commands\migrate\Migration;

/**
 * Class m190614_052025_update_quarantine_focuses_fk_localities
 */
class m190614_052025_update_quarantine_focuses_fk_localities extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_quarantines_focuses_id_locality', 'quarantines_focuses');

        $this->addForeignKey(
            'fk_quarantines_focuses_id_locality',
            'quarantines_focuses',
            'id_locality',
            'quarantines_localities',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190614_052025_update_quarantine_focuses_fk_localities cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190614_052025_update_quarantine_focuses_fk_localities cannot be reverted.\n";

        return false;
    }
    */
}
