<?php

use app\commands\migrate\Migration;

/**
 * Class m190425_125827_active_substances_drop_unique
 */
class m190425_125827_active_substances_drop_unique extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx_name', 'active_substances');
        $this->execute('alter table active_substances drop constraint active_substances_name_key');
        $this->execute('alter table active_substances drop constraint active_substances_name_en_key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190425_125827_active_substances_drop_unique cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190425_125827_active_substances_drop_unique cannot be reverted.\n";

        return false;
    }
    */
}
