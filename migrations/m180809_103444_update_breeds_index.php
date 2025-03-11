<?php

use yii\db\Migration;

/**
 * Class m180809_103444_update_breeds_index
 */
class m180809_103444_update_breeds_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE breeds ADD CONSTRAINT name_species_id_key UNIQUE (name, species_id)');
        $this->execute('ALTER TABLE breeds DROP CONSTRAINT IF EXISTS breeds_name_key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180809_103444_update_breeds_index cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180809_103444_update_breeds_index cannot be reverted.\n";

        return false;
    }
    */
}
