<?php

use app\commands\migrate\Migration;
use app\models\db\Shifts;

/**
 * Class m181019_112327_add_constraint_to_duration_in_shifts
 */
class m181019_112327_add_constraint_to_duration_in_shifts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = Shifts::tableName();
        $this->execute("ALTER TABLE {$tableName} ADD CONSTRAINT check_duration CHECK (duration >= 0 AND duration <= 1440)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181019_112327_add_constraint_to_duration_in_shifts cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181019_112327_add_constraint_to_duration_in_shifts cannot be reverted.\n";

        return false;
    }
    */
}
