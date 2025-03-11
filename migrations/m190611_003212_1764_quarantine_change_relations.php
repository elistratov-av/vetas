<?php

use app\commands\migrate\Migration;

/**
 * Class m190611_003212_1764_quarantine_change_relations
 */
class m190611_003212_1764_quarantine_change_relations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('quarantines_localities_to_focuses');

        $this->addColumn('quarantines_focuses', 'id_locality', $this->integer());

        $this->createIndex(
            'idx_quarantines_focuses_id_locality',
            'quarantines_focuses',
            'id_locality'
        );

        $this->addForeignKey(
            'fk_quarantines_focuses_id_locality',
            'quarantines_focuses',
            'id_locality',
            'quarantines_localities',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190611_003212_1764_quarantine_change_relations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190611_003212_1764_quarantine_change_relations cannot be reverted.\n";

        return false;
    }
    */
}
