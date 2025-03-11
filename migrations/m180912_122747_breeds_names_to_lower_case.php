<?php

use app\commands\migrate\Migration;

/**
 * Class m180912_122747_breeds_names_to_lower_case
 */
class m180912_122747_breeds_names_to_lower_case extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('UPDATE breeds SET name = LOWER(name)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180912_122747_breeds_names_to_lower_case cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180912_122747_breeds_names_to_lower_case cannot be reverted.\n";

        return false;
    }
    */
}
