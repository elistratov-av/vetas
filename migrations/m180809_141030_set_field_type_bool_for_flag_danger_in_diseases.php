<?php

use yii\db\Migration;

/**
 * Class m180809_141030_set_field_type_bool_for_flag_danger_in_diseases
 */
class m180809_141030_set_field_type_bool_for_flag_danger_in_diseases extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE diseases ALTER COLUMN flag_danger TYPE integer USING CASE WHEN flag_danger ~ '^\d+$' THEN flag_danger::integer ELSE null END;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180809_141030_set_field_type_bool_for_flag_danger_in_diseases cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180809_141030_set_field_type_bool_for_flag_danger_in_diseases cannot be reverted.\n";

        return false;
    }
    */
}
