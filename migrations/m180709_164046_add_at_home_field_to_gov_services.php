<?php

use yii\db\Migration;

/**
 * Class m180709_164046_add_at_home_field_to_gov_services
 */
class m180709_164046_add_at_home_field_to_gov_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE gov_services ADD COLUMN IF NOT EXISTS at_home BOOLEAN DEFAULT false;');
        $this->execute("COMMENT ON COLUMN gov_services.at_home IS 'Услуга оказывается на дому'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE gov_services DROP COLUMN IF EXISTS at_home;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m1807109164046_add_at_home_field_to_gov_services cannot be reverted.\n";

        return false;
    }
    */
}
