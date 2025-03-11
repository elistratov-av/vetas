<?php

use app\commands\migrate\Migration;

/**
 * Class m210127_135608_change_breed_name
 */
class m210127_135608_change_breed_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('breeds', ['name' => 'восточноевропейская овчарка'], ['name' => 'восточно-европейская овчарка']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('breeds', ['name' => 'восточно-европейская овчарка'], ['name' => 'восточноевропейская овчарка']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210127_135608_change_breed_name cannot be reverted.\n";

        return false;
    }
    */
}
