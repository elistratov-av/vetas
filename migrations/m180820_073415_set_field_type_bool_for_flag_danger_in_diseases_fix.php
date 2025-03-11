<?php

use yii\db\Migration;

/**
 * Class m180820_073415_set_field_type_bool_for_flag_danger_in_diseases_fix
 */
class m180820_073415_set_field_type_bool_for_flag_danger_in_diseases_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE diseases ALTER COLUMN flag_danger TYPE boolean USING flag_danger::boolean");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180820_073415_set_field_type_bool_for_flag_danger_in_diseases_fix cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180820_073415_set_field_type_bool_for_flag_danger_in_diseases_fix cannot be reverted.\n";

        return false;
    }
    */
}
