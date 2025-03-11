<?php

use app\commands\migrate\Migration;

/**
 * Class m180929_120344_set_visit_default_cooldown
 */
class m180929_120344_set_visit_default_cooldown extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table visits alter column cooldown set default 0;");
        $this->execute("UPDATE visits SET cooldown = 0, channel = channel WHERE cooldown IS NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("alter table visits alter column cooldown drop default;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180929_120344_set_visit_default_cooldown cannot be reverted.\n";

        return false;
    }
    */
}
