<?php

use yii\db\Migration;

/**
 * Class m180702_141225_species_breeds_11
 */
class m180702_141225_species_breeds_11 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('breeds', 'species_id', 'integer');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180702_141225_species_breeds_11 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180702_141225_species_breeds_11 cannot be reverted.\n";

        return false;
    }
    */
}
